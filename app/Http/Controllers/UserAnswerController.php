<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\UserAnswer;
use App\Helpers\BaseResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserAnswerController extends Controller
{
    /**
     * POST /user/exams/{exam}/answers
     * Store a single answer dengan auto-check is_correct.
     */
    public function store(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'question_id'     => 'required|exists:questions,id',
            'answer'          => 'nullable|string',
            'selected_option' => 'nullable|string',
        ]);

        $userId  = auth()->id();
        $answer  = $data['answer'] ?? $data['selected_option'] ?? '';

        // Gunakan selected_option jika answer kosong
        if (empty($answer) && !empty($data['selected_option'])) {
            $answer = $data['selected_option'];
        }

        // Auto-check: bandingkan dengan correct_answer di soal
        $question  = \App\Models\Questions::find($data['question_id']);
        $isCorrect = null;
        if ($question && $question->correct_answer !== null) {
            $isCorrect = strtolower(trim($answer)) === strtolower(trim($question->correct_answer));
        }

        $userAnswer = UserAnswer::updateOrCreate(
            [
                'user_id'     => $userId,
                'exam_id'     => $exam->id,
                'question_id' => $data['question_id'],
            ],
            [
                'answer'          => $answer,
                'selected_option' => $data['selected_option'] ?? $answer,
                'is_correct'      => $isCorrect,
                'answered_at'     => now(),
            ]
        );

        return response()->json($userAnswer, 201);
    }

    /**
     * POST /user/exams/{exam}/answers (batch)
     * Store multiple answers at once.
     */
    public function storeBatch(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'answers'                  => 'required|array',
            'answers.*.question_id'    => 'required|exists:questions,id',
            'answers.*.answer'         => 'nullable|string',
            'answers.*.selected_option' => 'nullable|string',
        ]);

        $userId  = auth()->id();
        $created = [];

        foreach ($data['answers'] as $ans) {
            $answer = UserAnswer::updateOrCreate(
                [
                    'user_id'     => $userId,
                    'exam_id'     => $exam->id,
                    'question_id' => $ans['question_id'],
                ],
                [
                    'answer'          => $ans['answer'] ?? $ans['selected_option'] ?? '',
                    'selected_option' => $ans['selected_option'] ?? $ans['answer'] ?? '',
                    'answered_at'     => now(),
                ]
            );
            $created[] = $answer;
        }

        return response()->json(['answers' => $created, 'count' => count($created)], 201);
    }

    /**
     * GET /user/exams/{exam}/my-answers
     * Siswa melihat jawaban sendiri beserta kunci dan pembahasan.
     */
    public function getMyAnswers(Exam $exam): JsonResponse
    {
        $userId = auth()->id();

        // Pastikan siswa pernah join ujian ini
        $userExam = \App\Models\UserExam::where('exam_id', $exam->id)
            ->where('user_id', $userId)
            ->first();

        if (!$userExam) {
            return BaseResponse::error('Anda belum pernah mengerjakan ujian ini', 403);
        }

        // Ambil jawaban siswa untuk ujian ini
        $myAnswers = UserAnswer::where('exam_id', $exam->id)
            ->where('user_id', $userId)
            ->with('question:id,question,type,options,correct_answer,explanation,order')
            ->get();

        $result = $myAnswers->map(fn($ua) => [
            'question_id'    => $ua->question_id,
            'question'       => $ua->question?->question,
            'type'           => $ua->question?->type,
            'options'        => $ua->question?->options,
            'correct_answer' => $ua->question?->correct_answer,
            'explanation'    => $ua->question?->explanation,
            'order'          => $ua->question?->order,
            'my_answer'      => $ua->answer ?? $ua->selected_option,
            'is_correct'     => $ua->is_correct,
            'answered_at'    => $ua->answered_at,
        ]);

        return BaseResponse::OK([
            'exam_id'    => $exam->id,
            'exam_title' => $exam->titles,
            'score'      => $userExam->score,
            'answers'    => $result,
        ], 'Jawaban berhasil diambil');
    }

    /**
     * GET /guru/exams/{exam}/answers
     * Review jawaban semua siswa untuk satu ujian (guru only).
     *
     * Response per soal:
     * - question + kunci jawaban + penjelasan
     * - daftar jawaban siswa beserta status benar/salah
     */
    public function getByExam(Exam $exam): JsonResponse
    {
        // Pastikan hanya guru pemilik ujian yang bisa akses
        if ($exam->created_by !== auth()->id()) {
            return BaseResponse::error('Unauthorized', 403);
        }

        // Ambil semua soal ujian beserta jawaban siswa
        $questions = $exam->questions()
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'question', 'type', 'options', 'correct_answer', 'explanation', 'order']);

        $questionsData = $questions->map(function ($q) use ($exam) {
            $answers = UserAnswer::where('exam_id', $exam->id)
                ->where('question_id', $q->id)
                ->with('user:id,name,email')
                ->get()
                ->map(fn($ua) => [
                    'user'       => $ua->user,
                    'answer'     => $ua->answer ?? $ua->selected_option,
                    'is_correct' => $ua->is_correct,
                    'answered_at' => $ua->answered_at,
                ]);

            return [
                'id'             => $q->id,
                'order'          => $q->order,
                'question'       => $q->question,
                'type'           => $q->type,
                'options'        => $q->options,
                'correct_answer' => $q->correct_answer,
                'explanation'    => $q->explanation,
                'answers'        => $answers,
                'answer_count'   => $answers->count(),
                'correct_count'  => $answers->where('is_correct', true)->count(),
            ];
        });

        return BaseResponse::OK([
            'exam_id'    => $exam->id,
            'exam_title' => $exam->titles,
            'questions'  => $questionsData,
        ], 'Jawaban siswa berhasil diambil');
    }
}
