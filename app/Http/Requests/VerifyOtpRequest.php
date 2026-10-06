<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nik' => ['required', 'digits_between:16,20', 'exists:users,nik'],
            'purpose' => ['required', Rule::in(['unlock_account', 'reset_password', 'reset_pin'])],
            'code' => ['required', 'digits:6'],
            'new_password' => ['required_if:purpose,reset_password', 'nullable', 'string', 'min:8', 'confirmed'],
            'new_pin' => ['required_if:purpose,reset_pin', 'nullable', 'digits:6'],
        ];
    }


    public function messages(): array
    {
        return [
            'nik.digits_between' => 'NIK harus terdiri dari 16 digit.',
        ];
    }
}
