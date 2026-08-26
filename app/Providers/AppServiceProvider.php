<?php

namespace App\Providers;

use App\Models\Nop;
use App\Models\Npwpd;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            'nop' => Nop::class,
            'npwpd' => Npwpd::class,
        ]);
    }
}
