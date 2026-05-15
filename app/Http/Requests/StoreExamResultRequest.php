<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamResultRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         return auth()->check() && auth()->user()->role === 'guru';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_exam_id'      => 'required|exists:user_exams,id',
            'exam_id'           => 'required|exists:exams,id',
            'user_id'           => 'required|exists:users,id',
            'total_question'    => 'required|integer|min:1',
            'correct_answer'    => 'required|integer|min:0',
            'wrong_answer'      => 'required|integer|min:0',
            'unanswered'        => 'required|integer|min:0',
            'score'             => 'required|numeric|min:0',
            'percentage'        => 'required|numeric|min:0|max:100',
            'is_passed'         => 'required|boolean',
            'detailed_answer'   => 'required|array',
            'time_spent_minutes'=> 'nullable|integer|min:0',
            'feedback'          => 'nullable|string',
        ];
    }
}
