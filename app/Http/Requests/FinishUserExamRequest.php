<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinishUserExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'score'                     => 'required|integer|min:0',
            'correct_answer'            => 'required|integer|min:0',
            'wrong_answer'              => 'required|integer|min:0',
            'unanswered'                => 'required|integer|min:0',
            'answers'                   => 'required|array',
            'answers.*.question_id'     => 'required|exists:questions,id',
            'answers.*.answer'          => 'nullable|string',
            'answers.*.selected_option' => 'nullable|string',
        ];
    }
}
