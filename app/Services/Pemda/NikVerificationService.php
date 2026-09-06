<?php

namespace App\Services\Pemda;

use App\Contracts\DukcapilServiceInterface;
use App\Enums\VerificationType;
use App\Exceptions\PemdaVerificationException;
use App\Models\VerificationLog;

class NikVerificationService
{
    public function __construct(
        private readonly DukcapilServiceInterface $dukcapil,
    ) {}

    /**
     * @return array{full_name: string}
     * @throws PemdaVerificationException
     */
    public function verify(string $nik, ?string $ipAddress = null): array
    {
        $data = $this->dukcapil->verifyNik($nik);

        VerificationLog::create([
            'id_user' => null,
            'type' => VerificationType::Nik,
            'value_checked' => $nik,
            'is_found' => $data !== null,
            'source' => 'dukcapil_dummy',
            'ip_address' => $ipAddress,
        ]);

        if ($data === null) {
            throw PemdaVerificationException::nikNotFound($nik);
        }

        return $data;
    }
}