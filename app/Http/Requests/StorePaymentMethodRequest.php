<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['credit_card', 'e_wallet', 'bank_transfer', 'qris'])],
            'provider' => ['required', 'string', 'max:50'],
            'token' => ['nullable', 'string'], // dummy: biar bisa dites manual tanpa SDK asli
            'masked_number' => ['nullable', 'string', 'max:30'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}