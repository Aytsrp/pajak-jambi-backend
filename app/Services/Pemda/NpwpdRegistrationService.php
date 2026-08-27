<?php

namespace App\Services\Pemda;

use App\Contracts\BapendaServiceInterface;
use App\Enums\VerificationType;
use App\Exceptions\PemdaVerificationException;
use App\Models\Npwpd;
use App\Models\User;

class NpwpdRegistrationService
{
    public function __construct(
        private readonly BapendaServiceInterface $bapenda,
    ) {}

    /**
     * @throws PemdaVerificationException
     */
    public function register(User $user, string $npwpdNumber, ?string $ipAddress = null): Npwpd
    {
        $trashed = Npwpd::onlyTrashed()
            ->where('id_user', $user->id_user)
            ->where('npwpd_number', $npwpdNumber)
            ->first();

        if ($trashed) {
            $trashed->restore();
            app(BillSyncService::class)->syncForNpwpd($trashed);
            return $trashed;
        }

        if ($user->npwpd) {
            throw PemdaVerificationException::npwpdAlreadyRegistered();
        }

        $data = $this->bapenda->findNpwpd($npwpdNumber);

        $user->verificationLogs()->create([
            'type' => VerificationType::Npwpd,
            'value_checked' => $npwpdNumber,
            'is_found' => $data !== null,
            'source' => 'bapenda_dummy',
            'ip_address' => $ipAddress,
        ]);

        if ($data === null) {
            throw PemdaVerificationException::npwpdNotFound($npwpdNumber);
        }

        $npwpd = $user->npwpd()->create([
            'npwpd_number' => $npwpdNumber,
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'],
            'owner_name' => $data['owner_name'],
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        app(BillSyncService::class)->syncForNpwpd($npwpd);

        return $npwpd;
    }
}