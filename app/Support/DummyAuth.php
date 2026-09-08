<?php

namespace App\Support;

class DummyAuth
{
    public static function enabled(): bool
    {
        return ! app()->isProduction();
    }

    public static function otpCode(): string
    {
        return (string) config('security.dummy.otp_code', '000000');
    }

    public static function passwordAccepted(string $plain): bool
    {
        return in_array($plain, config('security.dummy.passwords', []), true);
    }

    public static function pinAccepted(string $plain): bool
    {
        return in_array($plain, config('security.dummy.pins', []), true);
    }
}
