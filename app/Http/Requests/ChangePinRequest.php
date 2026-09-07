<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => ['required', 'digits:6'],
            'new_pin' => ['required', 'digits:6', 'confirmed', 'different:current_pin'],
        ];
    }
}