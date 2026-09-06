<?php

namespace App\Providers;

use App\Contracts\BapendaServiceInterface;
use App\Contracts\DukcapilServiceInterface;
use App\Services\Dummy\DummyBapendaService;
use App\Services\Dummy\DummyDukcapilService;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class PemdaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BapendaServiceInterface::class, function () {
            return match (config('pemda_services.driver')) {
                'real' => throw new RuntimeException('RealBapendaService belum diimplementasikan.'),
                default => new DummyBapendaService(),
            };
        });

        $this->app->bind(DukcapilServiceInterface::class, function () {
            return match (config('pemda_services.driver')) {
                'real' => throw new RuntimeException('RealDukcapilService belum diimplementasikan.'),
                default => new DummyDukcapilService(),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
