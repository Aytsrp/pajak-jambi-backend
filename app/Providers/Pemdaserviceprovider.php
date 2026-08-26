<?php

namespace App\Providers;

use App\Contracts\BapendaServiceInterface;
use App\Contracts\DukcapilServiceInterface;
use App\Services\Dummy\DummyBapendaService;
use App\Services\Dummy\DummyDukcapilService;
use Illuminate\Support\ServiceProvider;

/**
 * Titik SATU-SATUNYA tempat menentukan implementasi mana yang dipakai
 * (dummy vs real) untuk integrasi Dukcapil & Bapenda.
 *
 * Controller/Service lain cukup type-hint interface-nya
 * (DukcapilServiceInterface / BapendaServiceInterface), Laravel otomatis
 * suntikkan implementasi yang benar sesuai binding di bawah ini.
 */
class PemdaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $driver = config('pemda_services.driver', 'dummy');

        match ($driver) {
            // Tambahkan case 'real' => $this->registerRealServices(), di sini nanti
            // kalau API resmi Bapenda/Dukcapil sudah tersedia.
            default => $this->registerDummyServices(),
        };
    }

    private function registerDummyServices(): void
    {
        $this->app->bind(DukcapilServiceInterface::class, DummyDukcapilService::class);
        $this->app->bind(BapendaServiceInterface::class, DummyBapendaService::class);
    }
}