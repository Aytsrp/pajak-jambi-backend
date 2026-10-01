<?php

namespace App\Providers;

use App\Contracts\BapendaServiceInterface;
use App\Contracts\DukcapilServiceInterface;
use App\Services\Oracle\OracleDukcapilService;
use App\Services\Pemda\HybridBapendaService;
use Illuminate\Support\ServiceProvider;

class PemdaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // NIK: selalu dari Oracle.
        $this->app->bind(DukcapilServiceInterface::class, OracleDukcapilService::class);

        // NOP: Oracle. NPWPD: dummy (sementara). Lihat HybridBapendaService.
        $this->app->bind(BapendaServiceInterface::class, HybridBapendaService::class);
    }
}