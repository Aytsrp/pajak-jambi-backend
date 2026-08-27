<?php

namespace App\Listeners;

use App\Enums\NotificationType;
use App\Enums\TransactionStatus;
use App\Events\TransactionCompleted;

class CreateTransactionNotification
{
    public function handle(TransactionCompleted $event): void
    {
        $transaction = $event->transaction;
        $isSuccess = $transaction->status === TransactionStatus::Success;

        $transaction->user->notifications()->create([
            'type' => $isSuccess ? NotificationType::PaymentSuccess : NotificationType::PaymentFailed,
            'title' => $isSuccess ? 'Pembayaran Berhasil' : 'Pembayaran Gagal',
            'body' => $isSuccess
                ? "Pembayaran {$transaction->tax_type->label()} sebesar Rp " . number_format((float) $transaction->amount, 0, ',', '.') . " berhasil diproses."
                : "Pembayaran {$transaction->tax_type->label()} sebesar Rp " . number_format((float) $transaction->amount, 0, ',', '.') . " gagal diproses. Silakan coba lagi.",
            'is_read' => false,
            'sent_at' => now(),
        ]);
    }
}