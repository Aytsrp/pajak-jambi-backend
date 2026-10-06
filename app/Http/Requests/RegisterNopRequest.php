<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterNopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nop_number' => ['required', 'string', 'digits:18'],
        ];
    }

    public function messages(): array
    {
        return [
            'nop_number.digits' => 'NOP harus terdiri dari 18 digit angka.',
        ];
    }
}