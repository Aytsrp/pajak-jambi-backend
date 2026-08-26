<?php

namespace App\Enums;

enum NotificationType: string
{
    case BillReminder = 'bill_reminder';
    case PenaltyWarning = 'penalty_warning';
    case PaymentSuccess = 'payment_success';
    case PaymentFailed = 'payment_failed';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::BillReminder => 'Pengingat Tagihan',
            self::PenaltyWarning => 'Peringatan Denda',
            self::PaymentSuccess => 'Pembayaran Berhasil',
            self::PaymentFailed => 'Pembayaran Gagal',
            self::General => 'Umum',
        };
    }
}