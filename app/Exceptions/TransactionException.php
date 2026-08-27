<?php

namespace App\Exceptions;

use Exception;

class TransactionException extends Exception
{
    public static function billNotFound(): self
    {
        return new self('Tagihan tidak ditemukan atau bukan milik Anda.', 404);
    }

    public static function billAlreadyPaid(): self
    {
        return new self('Tagihan ini sudah lunas.', 422);
    }

    public static function paymentMethodNotFound(): self
    {
        return new self('Metode pembayaran tidak ditemukan atau bukan milik Anda.', 404);
    }

    public static function transactionNotPending(): self
    {
        return new self('Transaksi ini sudah tidak berstatus menunggu pembayaran.', 422);
    }

    public static function pinLocked(): self
    {
        return new self('PIN terkunci sementara karena terlalu banyak percobaan gagal. Silakan minta OTP reset PIN.', 423);
    }

    public static function invalidPin(): self
    {
        return new self('PIN salah.', 422);
    }

    public static function gatewayFailed(string $reason): self
    {
        return new self("Pembayaran gagal: {$reason}", 402);
    }
}