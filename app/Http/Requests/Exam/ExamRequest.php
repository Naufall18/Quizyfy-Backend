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
            'titles'            => 'required|string|max:255',
            'description'       => 'nullable|string',
            'category_id'       => 'nullable|exists:categories,id',
            'start_time'        => 'required|date',
            'end_time'          => 'required|date|after:start_time',
            'duration_minutes'  => 'required|integer|min:1',
            'total_questions'   => 'required|integer|min:0',
            // Terima kkm_score ATAU passing_grade
            'kkm_score'         => 'nullable|integer|min:0|max:100',
            'passing_grade'     => 'nullable|integer|min:0|max:100',
            'max_attempts'      => 'nullable|integer|min:1',
            'status'            => 'nullable|in:draft,aktif,nonaktif,berlangsung,selesai',
            'show_result'       => 'nullable|boolean',
            'shuffle_question'  => 'nullable|boolean',
            'shuffle_option'    => 'nullable|boolean',
            'instructions'      => 'nullable|string',
            'bank_soal_id'      => 'nullable|string',
        ];
    }

    /**
     * Prepare the data for validation — normalize passing_grade → kkm_score
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('passing_grade') && !$this->has('kkm_score')) {
            $this->merge(['kkm_score' => $this->passing_grade]);
        }
    }
}
