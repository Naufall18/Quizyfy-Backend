<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class GoogleLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_token'  => 'required|string',
            'email'     => 'required|email|max:255',
            'name'      => 'required|string|max:255',
            'photo_url' => 'nullable|string|url',
        ];
    }

    public function messages(): array
    {
        return [
            'id_token.required' => 'ID Token Google wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'name.required' => 'Nama wajib diisi',
            'photo_url.url' => 'Format URL foto tidak valid',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
