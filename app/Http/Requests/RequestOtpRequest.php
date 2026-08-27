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
            'nik' => ['required', 'digits:16', 'exists:users,nik'],
            'purpose' => ['required', Rule::in(['unlock_account', 'reset_password', 'reset_pin'])],
            'channel' => ['required', Rule::in(['email', 'sms'])],
        ];
    }
}