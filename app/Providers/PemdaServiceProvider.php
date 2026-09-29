<?php

namespace App\Providers;

use App\Contracts\BapendaServiceInterface;
use App\Contracts\DukcapilServiceInterface;
use App\Services\Dummy\DummyBapendaService;
use App\Services\Oracle\OracleDukcapilService;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class PemdaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // NIK: selalu dari database Oracle.
        $this->app->bind(DukcapilServiceInterface::class, OracleDukcapilService::class);

        // NOP & NPWPD: masih dummy (dikerjakan di tahap berikutnya).
        $this->app->bind(BapendaServiceInterface::class, function () {
            return match (config('pemda_services.driver')) {
                'real' => throw new RuntimeException('RealBapendaService belum diimplementasikan.'),
                default => new DummyBapendaService(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}