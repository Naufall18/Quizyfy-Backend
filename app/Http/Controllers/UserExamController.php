<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinishUserExamRequest;
use App\Http\Requests\StartUserExamRequest;
use App\Models\Exam;
use App\Models\UserAnswer;
use App\Models\UserExam;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserExamController extends Controller
{
    public function start(StartUserExamRequest $request, Exam $exam)
    {
        $user = Auth::user();

        if (
            $exam->status !== 'aktif' ||
            ($exam->start_time && Carbon::now()->lt($exam->start_time)) ||
            ($exam->end_time   && Carbon::now()->gt($exam->end_time))
        ) {
            return response()->json(['message' => 'Ujian tidak tersedia'], 403);
        }

        // Check if already in progress
        $existing = UserExam::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if ($existing) {
            $remaining = Carbon::now()->diffInSeconds($existing->deadline, false);
            return response()->json([
                'message'           => 'Ujian sudah dimulai',
                'user_exam'         => $existing,
                'remaining_seconds' => max(0, $remaining),
            ], 200);
        }

        $attemptNumber = UserExam::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->count() + 1;

        $deadline = Carbon::now()->addMinutes($exam->duration_minutes);

        $userExam = UserExam::create([
            'user_id'        => $user->id,
            'exam_id'        => $exam->id,
            'started_at'     => Carbon::now(),
            'deadline'       => $deadline,
            'status'         => 'in_progress',
            'attempt_number' => $attemptNumber,
        ]);

        return response()->json([
            'message'           => 'Ujian dimulai',
            'user_exam'         => $userExam,
            'remaining_seconds' => Carbon::now()->diffInSeconds($deadline, false),
        ], 201);
    }

    public function status(Request $request, Exam $exam)
    {
        $user = Auth::user();
        $userExam = UserExam::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->where('status', 'in_progress')
            ->latest()
            ->firstOrFail();

        $now       = Carbon::now();
        $remaining = $userExam->deadline
            ? $now->diffInSeconds($userExam->deadline, false)
            : null;

        if ($remaining !== null && $remaining <= 0) {
            $userExam->update(['status' => 'time_up', 'finished_at' => $now]);
            return response()->json(['status' => 'time_up', 'remaining_seconds' => 0]);
        }

        return response()->json([
            'status'            => $userExam->status,
            'remaining_seconds' => $remaining,
        ]);
    }

    public function finish(FinishUserExamRequest $request, Exam $exam)
    {
        $user = Auth::user();

        $userExam = UserExam::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->whereIn('status', ['in_progress', 'time_up'])
            ->latest()
            ->firstOrFail();

        // Auto-calculate score from stored UserAnswers
        $answers = UserAnswer::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->get();

        $totalQuestions = $exam->questions()->where('is_active', true)->count();
        $correct  = 0;
        $wrong    = 0;

        foreach ($answers as $ans) {
            $question = $ans->question;
            if (!$question) continue;

            if ($question->type === 'essay') {
                // Essay: tidak auto-grade, skip
                continue;
            }

            // Compare selected_option with correct_answer field
            $correctOption = $question->correct_answer ?? $question->answer ?? null;
            if ($correctOption !== null) {
                $userSelected = $ans->selected_option ?? $ans->answer;
                if ((string)$userSelected === (string)$correctOption) {
                    $correct++;
                } else {
                    $wrong++;
                }
            }
        }

        $unanswered = max(0, $totalQuestions - $answers->count());
        $score      = $totalQuestions > 0
            ? (int) round(($correct / $totalQuestions) * 100)
            : 0;

        // If client sends score (override auto-calculation)
        $data     = $request->validated();
        $finalScore  = $data['score']          ?? $score;
        $finalCorrect = $data['correct_answer'] ?? $correct;
        $finalWrong   = $data['wrong_answer']   ?? $wrong;
        $finalUnanswered = $data['unanswered']  ?? $unanswered;

        $userExam->update([
            'finished_at'     => Carbon::now(),
            'status'          => 'completed',
            'score'           => $finalScore,
            'correct_answers' => $finalCorrect,
            'wrong_answers'   => $finalWrong,
            'unanswered'      => $finalUnanswered,
        ]);

        return response()->json([
            'message'   => 'Ujian telah selesai',
            'user_exam' => $userExam->fresh(),
            'score'     => $finalScore,
            'correct'   => $finalCorrect,
            'wrong'     => $finalWrong,
            'unanswered' => $finalUnanswered,
        ]);
    }

    /**
     * GET /user/exams/{exam}/result
     * Ambil hasil ujian yang sudah selesai
     */
    public function result(Request $request, Exam $exam)
    {
        $user = Auth::user();

        $userExam = UserExam::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->whereIn('status', ['completed', 'time_up'])
            ->latest()
            ->first();

        if (!$userExam) {
            return response()->json(['message' => 'Belum ada hasil untuk ujian ini'], 404);
        }

        // Load answers with question details
        $answers = UserAnswer::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->with('question.options')
            ->get()
            ->map(function ($ans) {
                $question = $ans->question;
                if (!$question) return null;

                $opts = [];
                if ($question->options) {
                    foreach ($question->options as $opt) {
                        $isCorrect = $opt->is_correct ?? false;
                        $isSelected = (string)($ans->selected_option ?? $ans->answer) === (string)$opt->id;
                        $status = 'neutral';
                        if ($isCorrect)  $status = 'correct';
                        if ($isSelected && !$isCorrect) $status = 'wrong';
                        if ($isCorrect && $isSelected)  $status = 'correct';

                        $opts[] = [
                            'text'       => $opt->option_text ?? $opt->text ?? '',
                            'is_correct' => $isCorrect,
                            'is_selected' => $isSelected,
                            'status'     => $status,
                        ];
                    }
                }

                return [
                    'order'       => $question->order ?? $question->id,
                    'question'    => $question->question ?? $question->text ?? '',
                    'type'        => $question->type ?? 'multiple_choice',
                    'options'     => $opts,
                    'user_answer' => $ans->answer,
                    'is_correct'  => $ans->is_correct ?? null,
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'score'        => $userExam->score ?? 0,
            'correct'      => $userExam->correct_answers ?? 0,
            'wrong'        => $userExam->wrong_answers ?? 0,
            'unanswered'   => $userExam->unanswered ?? 0,
            'submitted_at' => $userExam->finished_at,
            'exam'         => [
                'id'    => $exam->id,
                'titles' => $exam->titles,
            ],
            'answers' => $answers,
        ]);
    }
}
