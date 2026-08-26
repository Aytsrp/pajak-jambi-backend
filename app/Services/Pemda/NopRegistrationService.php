<?php

namespace App\Services\Pemda;

use App\Contracts\BapendaServiceInterface;
use App\Enums\VerificationType;
use App\Exceptions\PemdaVerificationException;
use App\Models\Nop;
use App\Models\User;

class NopRegistrationService
{
    public function __construct(
        private readonly BapendaServiceInterface $bapenda,
    ) {}

    /**
     * Daftarkan NOP baru ke akun user.
     * @throws PemdaVerificationException kalau NOP tidak ditemukan di Bapenda
     */
    public function register(User $user, string $nopNumber, ?string $ipAddress = null): Nop
    {
        // Cek dulu, jangan sampai user daftar NOP yang sama 2x
        $existing = $user->nops()->where('nop_number', $nopNumber)->first();
        if ($existing) {
            return $existing;
        }

        $data = $this->bapenda->findNop($nopNumber);

        $user->verificationLogs()->create([
            'type' => VerificationType::Nop,
            'value_checked' => $nopNumber,
            'is_found' => $data !== null,
            'source' => 'bapenda_dummy',
            'ip_address' => $ipAddress,
        ]);

        if ($data === null) {
            throw PemdaVerificationException::nopNotFound($nopNumber);
        }

        $nop = $user->nops()->create([
            'nop_number' => $nopNumber,
            'object_name' => $data['object_name'],
            'owner_name' => $data['owner_name'],
            'object_address' => $data['object_address'],
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        // Langsung tarik tagihan begitu terdaftar, supaya user langsung lihat status
        app(BillSyncService::class)->syncForNop($nop);

        return $nop;
    }
}