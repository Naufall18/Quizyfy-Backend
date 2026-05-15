<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankRequest extends FormRequest
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
            'exam_id'     => 'nullable|exists:exams,id',
            'category_id' => 'nullable|exists:categories,id',
            'search'      => 'nullable|string|max:100',
            'shuffle'     => 'in:0,1',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ];
    }
}
