<?php

namespace App\Providers;

use App\Contracts\BapendaServiceInterface;
use App\Services\Dummy\DummyBapendaService;
use Illuminate\Support\ServiceProvider;

class PemdaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BapendaServiceInterface::class, function () {
            return match (config('pemda_services.driver')) {
                'real' => throw new \RuntimeException(
                    'RealBapendaService belum diimplementasikan. Set PEMDA_SERVICE_DRIVER=dummy di .env.'
                ),
                default => new DummyBapendaService(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}