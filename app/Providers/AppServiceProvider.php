<?php

namespace App\Providers;

use App\Contracts\OtpSenderInterface;
use App\Contracts\BankGatewayInterface;
use App\Contracts\QrisGatewayInterface;
use App\Events\TransactionCompleted;
use App\Listeners\CreateTransactionNotification;
use App\Models\Nop;
use App\Models\Npwpd;
use App\Services\Security\RealOtpSender;
use App\Services\Dummy\DummyBankGatewayService;
use App\Services\Dummy\DummyQrisGatewayService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OtpSenderInterface::class, RealOtpSender::class);
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

        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $max = (int) config('security.rate_limit.login_per_minute');

            return [
                Limit::perMinute($max)->by($request->ip()),
                Limit::perMinute($max)->by('nik:' . ($request->input('nik') ?: $request->ip())),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute((int) config('security.rate_limit.register_per_minute'))
                ->by($request->ip());
        });

        RateLimiter::for('otp-request', function (Request $request) {
            $max = (int) config('security.rate_limit.otp_per_minute');

            return [
                Limit::perMinute($max)->by($request->ip()),
                Limit::perMinute($max)->by('nik:' . ($request->input('nik') ?: $request->ip())),
            ];
        });
    }
}

