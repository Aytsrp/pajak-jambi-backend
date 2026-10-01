<?php

namespace App\Services\Pemda;

use App\Contracts\BapendaServiceInterface;
use App\Services\Dummy\DummyBapendaService;
use App\Services\Oracle\OracleBapendaService;

/**
 * NOP: selalu dari Oracle.
 * NPWPD: masih dummy sampai skema Oracle-nya dikonfirmasi (menyusul).
 *
 * Dipisah per method (bukan satu driver global) supaya NOP bisa pindah ke Oracle
 * duluan tanpa menunggu NPWPD selesai -- sama seperti yang dilakukan untuk NIK.
 */
class HybridBapendaService implements BapendaServiceInterface
{
    public function __construct(
        private readonly OracleBapendaService $oracle,
        private readonly DummyBapendaService $dummy,
    ) {}

    public function findNop(string $nopNumber): ?array
    {
        return $this->oracle->findNop($nopNumber);
    }

    public function getBillsForNop(string $nopNumber): array
    {
        return $this->oracle->getBillsForNop($nopNumber);
    }

    public function findNpwpd(string $npwpdNumber): ?array
    {
        return $this->dummy->findNpwpd($npwpdNumber);
    }

    public function getBillsForNpwpd(string $npwpdNumber): array
    {
        return $this->dummy->getBillsForNpwpd($npwpdNumber);
    }
}