<?php

namespace App\Exceptions;

use Exception;

class PemdaVerificationException extends Exception
{
    public static function nopNotFound(string $nopNumber): self
    {
        return new self("NOP {$nopNumber} tidak ditemukan di sistem Bapenda Kota Jambi.", 404);
    }

    public static function npwpdNotFound(string $npwpdNumber): self
    {
        return new self("NPWPD {$npwpdNumber} tidak ditemukan di sistem Bapenda Kota Jambi.", 404);
    }

    public static function npwpdAlreadyRegistered(): self
    {
        return new self('Akun ini sudah memiliki NPWPD terdaftar. Satu akun hanya boleh memiliki satu NPWPD.', 422);
    }
}