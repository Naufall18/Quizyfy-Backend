<?php

namespace App\Http\Requests\UserAnswer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserAnswer extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isTeacher();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'is_correct' => 'required|boolean',
            'points_earned' => 'required|integer|min:8'
        ];
    }
}
