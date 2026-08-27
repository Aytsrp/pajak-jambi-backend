<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiateTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id_bill' => ['required', 'integer'],
            'id_payment' => ['required', 'integer'],
            'idempotency_key' => ['required', 'string', 'max:100'],
        ];
    }
}