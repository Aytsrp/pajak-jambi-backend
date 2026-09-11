<?php

namespace App\Contracts;

use App\Enums\OtpChannel;
use App\Models\User;

interface OtpSenderInterface
{
    /**
     * Kirim kode OTP ke user lewat channel yang dipilih.
     * Return true kalau terkirim, false kalau gagal (misal SMTP error / nomor kosong).
     */
    public function send(User $user, OtpChannel $channel, string $plainCode): bool;
}