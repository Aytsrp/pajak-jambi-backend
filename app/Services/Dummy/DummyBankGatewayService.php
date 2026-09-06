<?php

namespace App\Services\Dummy;

use App\Contracts\BankGatewayInterface;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

class DummyBankGatewayService implements BankGatewayInterface
{
    public function createVirtualAccount(Transaction $transaction): array
    {
        $prefix = $transaction->bank_code->vaPrefix();
        $vaNumber = $prefix . str_pad((string) $transaction->id_transactions, 12, '0', STR_PAD_LEFT);

        return [
            'va_number' => $vaNumber,
            'expired_at' => Carbon::now()->addHours(24),
        ];
    }

    public function verifyIncomingRequest(array $headers, array $payload): bool
    {
        return ($headers['x-api-key'] ?? null) === config('bank_gateway.dummy_shared_secret');
    }

    public function formatInquiryResponse(Transaction $transaction): array
    {
        return [
            'response_code' => '00',
            'response_message' => 'Sukses',
            'va_number' => $transaction->va_number,
            'customer_name' => $transaction->user->full_name,
            'bill_description' => $transaction->tax_type->label() . ' - ' . $transaction->bill->tax_period,
            'total_amount' => number_format((float) $transaction->amount, 2, '.', ''),
            'currency' => 'IDR',
        ];
    }

    public function formatPaymentResponse(Transaction $transaction, bool $success): array
    {
        return [
            'response_code' => $success ? '00' : '01',
            'response_message' => $success ? 'Pembayaran berhasil dicatat' : 'Gagal memproses pembayaran',
            'va_number' => $transaction->va_number,
            'transaction_ref' => $transaction->transaction_ref,
        ];
    }
}