<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBiodataRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $this->user()->id,
            'phone_number' => [
                'sometimes',
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/'
            ],
            'gender' => 'sometimes|required|string|in:male,female',
        ];
    }
}
