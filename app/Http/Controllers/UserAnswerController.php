<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\UserAnswer;
use Illuminate\Http\Request;

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
}
