<?php

namespace App\Http\Requests;

use App\Enums\BankCode;
use App\Enums\PaymentChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitiateTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id_bill' => ['required', 'integer'],
            'payment_channel' => ['required', Rule::in(array_column(PaymentChannel::cases(), 'value'))],
            'bank_code' => [
                'required_if:payment_channel,bank_transfer',
                Rule::in(array_column(BankCode::cases(), 'value')),
            ],
            'idempotency_key' => ['required', 'string', 'max:100'],
        ];
    }
}