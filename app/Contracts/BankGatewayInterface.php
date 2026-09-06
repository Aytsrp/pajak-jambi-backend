<?php

namespace App\Contracts;

use App\Models\Transaction;
use Illuminate\Support\Carbon;

interface BankGatewayInterface
{
    /**
     * @return array{va_number: string, expired_at: Carbon}
     */
    public function createVirtualAccount(Transaction $transaction): array;

    public function verifyIncomingRequest(array $headers, array $payload): bool;

    public function formatInquiryResponse(Transaction $transaction): array;

    public function formatPaymentResponse(Transaction $transaction, bool $success): array;
}