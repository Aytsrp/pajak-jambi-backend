<?php

namespace App\Http\Requests;

use App\Enums\BankCode;
use App\Enums\PaymentChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_column(PaymentChannel::cases(), 'value'))],
            'provider' => [
                'required',
                'string',
                'max:50',
                Rule::in([...array_column(BankCode::cases(), 'value'), 'qris']),
            ],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}