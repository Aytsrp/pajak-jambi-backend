<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => ['required', 'digits_between:16,20'],
            'password' => ['required', 'string'],
        ];
    }

        public function messages(): array
    {
        return [
            'nik.digits_between' => 'NIK harus terdiri dari 16 digit.',
        ];
    }
}