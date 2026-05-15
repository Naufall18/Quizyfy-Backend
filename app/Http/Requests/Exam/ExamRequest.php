<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class ExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'titles' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:1',
            'total_questions' => 'required|integer|min:0',
            'kkm_score' => 'required|integer|max:100',
            'max_attempts' => 'integer|min:1',
            'status' => 'in:draft,aktif,nonaktif,berlangsung,selesai',
            'show_result' => 'boolean',
            'shuffle_question' => 'boolean',
            'shuffle_option' => 'boolean',
            'instructions' => 'nullable|string',
        ];
    }
}
