<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Fix: was false — blocked all update requests with 403
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'titles'           => 'required|string|max:255',
            'description'      => 'nullable|string',
            // category_id dibuat nullable karena frontend tidak mengirimkan field ini
            'category_id'      => 'nullable|exists:categories,id',
            'start_time'       => 'required|date',
            'end_time'         => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:1',
            'total_questions'  => 'required|integer|min:0',
            // Terima kkm_score ATAU passing_grade (frontend mengirim passing_grade)
            'kkm_score'        => 'nullable|integer|min:0|max:100',
            'passing_grade'    => 'nullable|integer|min:0|max:100',
            'status'           => 'nullable|in:draft,aktif,nonaktif,berlangsung,selesai',
            'bank_soal_id'     => 'nullable|string',
        ];
    }

    /**
     * Prepare the data for validation — normalize passing_grade → kkm_score
     */
    protected function prepareForValidation(): void
    {
        // Jika frontend mengirim passing_grade tapi tidak kkm_score, salin nilainya
        if ($this->has('passing_grade') && !$this->has('kkm_score')) {
            $this->merge(['kkm_score' => $this->passing_grade]);
        }
    }
}
