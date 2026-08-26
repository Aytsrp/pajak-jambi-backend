<?php

namespace App\Enums;

enum OtpPurpose: string
{
    case UnlockAccount = 'unlock_account';
    case ResetPassword = 'reset_password';
    case ResetPin = 'reset_pin';

    public function label(): string
    {
        return match ($this) {
            self::UnlockAccount => 'Buka Kunci Akun',
            self::ResetPassword => 'Reset Password',
            self::ResetPin => 'Reset PIN',
        };
    }
}