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
     * Store a single answer. Can be called multiple times (one per question).
     */
    public function store(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'question_id'     => 'required|exists:questions,id',
            'answer'          => 'nullable|string',
            'selected_option' => 'nullable|string',
        ]);

        $data['user_id']     = auth()->id();
        $data['exam_id']     = $exam->id;
        $data['answered_at'] = now();

        // Use answer or selected_option
        if (empty($data['answer']) && !empty($data['selected_option'])) {
            $data['answer'] = $data['selected_option'];
        }

        $answer = UserAnswer::updateOrCreate(
            [
                'user_id'     => $data['user_id'],
                'exam_id'     => $data['exam_id'],
                'question_id' => $data['question_id'],
            ],
            $data
        );

        return response()->json($answer, 201);
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
