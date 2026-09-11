<?php

namespace App\Exceptions;

use Exception;

class OtpException extends Exception
{
    public static function notFound(): self
    {
        return new self('Kode OTP tidak ditemukan, silakan minta kode baru.', 404);
    }

    public static function expired(): self
    {
        return new self('Kode OTP sudah kedaluwarsa, silakan minta kode baru.', 410);
    }

    public static function invalidCode(): self
    {
        return new self('Kode OTP salah.', 422);
    }

    public static function tooManyAttempts(): self
    {
        return new self('Terlalu banyak percobaan salah, silakan minta kode baru.', 429);
    }

    public static function sendFailed(): self
    {
        return new self('Kode OTP gagal dikirim. Silakan coba lagi.', 503);
    }
}