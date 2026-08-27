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
            'nik' => ['required', 'digits:16', 'exists:users,nik'],
            'purpose' => ['required', Rule::in(['unlock_account', 'reset_password', 'reset_pin'])],
            'code' => ['required', 'digits:6'],

            // Wajib diisi kalau purpose = reset_password / reset_pin
            'new_password' => ['required_if:purpose,reset_password', 'nullable', 'string', 'min:8', 'confirmed'],
            'new_pin' => ['required_if:purpose,reset_pin', 'nullable', 'digits:6'],
        ];
    }
}