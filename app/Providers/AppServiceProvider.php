<?php

namespace App\Providers;

use App\Contracts\OtpSenderInterface;
use App\Contracts\BankGatewayInterface;
use App\Contracts\QrisGatewayInterface;
use App\Events\TransactionCompleted;
use App\Listeners\CreateTransactionNotification;
use App\Models\Nop;
use App\Models\Npwpd;
use App\Services\Dummy\DummyOtpSender;
use App\Services\Dummy\DummyBankGatewayService;
use App\Services\Dummy\DummyQrisGatewayService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OtpSenderInterface::class, DummyOtpSender::class);
        $this->app->bind(BankGatewayInterface::class, DummyBankGatewayService::class);
        $this->app->bind(QrisGatewayInterface::class, function(){
            return match (config('qris_gateway.driver')){
                default => new DummyQrisGatewayService(),
            };
        });
    }

    public function boot(): void
    {
        Relation::morphMap([
            'nop' => Nop::class,
            'npwpd' => Npwpd::class,
        ]);

        Event::listen(TransactionCompleted::class, CreateTransactionNotification::class);
    }
}