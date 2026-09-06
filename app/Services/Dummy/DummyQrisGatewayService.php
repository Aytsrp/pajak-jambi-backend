<?php

namespace App\Services\Dummy;

use App\Contracts\QrisGatewayInterface;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class DummyQrisGatewayService implements QrisGatewayInterface
{
    public function generate(Transaction $transaction): array
    {
        $qrString = 'DUMMYQRIS|' . $transaction->transaction_ref . '|' . (int) $transaction->amount;

        return [
            'qr_string' => $qrString,
            'qr_image_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrString),
            'expired_at' => Carbon::now()->addMinutes(15), // QRIS lazimnya lebih pendek dari VA
        ];
    }

    public function verifyNotification(array $payload): bool
    {
        return ($payload['secret'] ?? null) === config('qris_gateway.dummy_shared_secret');
    }

    public function parseNotification(array $payload): array
    {
        return [
            'order_id' => $payload['order_id'] ?? '',
            'is_success' => ($payload['status'] ?? '') === 'success',
            'is_failed' => ($payload['status'] ?? '') === 'failed',
            'gateway_ref' => $payload['gateway_ref'] ?? ('QRIS-' . strtoupper(Str::random(10))),
        ];
    }
}