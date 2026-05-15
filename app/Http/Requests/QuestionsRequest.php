<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionsRequest extends FormRequest
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

            'question' => 'required|string',
            'type' => 'in:multiple,essay,true_false',
            'options' => 'array',
            'options.*' => 'string',
            'correct_answer' => 'required|string',
            'explanation' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'order' => 'nullable|string',
            'is_active' => 'required|boolean'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $type = $this->input('type');

            if ($type === 'multiple') {
                if (!$this->has('options') || !is_array($this->input('options')) || count($this->input('options')) < 2) {
                    $validator->errors()->add('options', 'Options wajib diisi minimal 2 pilihan untuk multiple choice');
                }
                if (!$this->filled('correct_answer')) {
                    $validator->errors()->add('correct_answer', 'Correct answer wajib diisi untuk multiple choice');
                } elseif (!in_array($this->input('correct_answer'), $this->input('options', []))) {
                    $validator->errors()->add('correct_answer', 'Correct answer harus salah satu dari pilihan (options)');
                }
            }

            if (in_array($type, ['essay', 'true_false'])) {
                if ($this->filled('options')) {
                    $validator->errors()->add('options', 'Options tidak boleh diisi untuk essay atau true/false');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'question.required'       => 'Pertanyaan wajib diisi',
            'question.string'         => 'Pertanyaan harus berupa string',
            
            'type.in'                 => 'Tipe pertanyaan tidak valid',
            'correct_answer.required' => 'Jawaban benar wajib diisi',
            'image.image'             => 'File harus berupa gambar',
            'image.max'               => 'Ukuran gambar maksimal 2MB',
            'is_active.required'      => 'Status aktif tidak boleh kosong',
        ];
    }
}
