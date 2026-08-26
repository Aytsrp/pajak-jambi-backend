<?php

namespace App\Providers;

use App\Contracts\OtpSenderInterface;
use App\Models\Nop;
use App\Models\Npwpd;
use App\Services\Dummy\DummyOtpSender;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OtpSenderInterface::class, DummyOtpSender::class);
    }

    public function boot(): void
    {
        Relation::morphMap([
            'nop' => Nop::class,
            'npwpd' => Npwpd::class,
        ]);
    }
}