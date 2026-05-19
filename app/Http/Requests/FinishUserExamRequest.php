<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinishUserExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // All fields optional — backend auto-calculates score from stored answers
            'score'          => 'nullable|integer|min:0',
            'correct_answer' => 'nullable|integer|min:0',
            'wrong_answer'   => 'nullable|integer|min:0',
            'unanswered'     => 'nullable|integer|min:0',
            'answers'        => 'nullable|array',
        ];
    }
}
