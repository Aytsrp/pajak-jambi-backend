<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestOtpRequest extends FormRequest
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
            'channel' => ['required', Rule::in(['email', 'sms'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.digits_between' => 'NIK harus terdiri dari 16 digit.',
        ];
    }
}
