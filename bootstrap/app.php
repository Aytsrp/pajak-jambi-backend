<?php

use App\Exceptions\PemdaVerificationException;
use App\Exceptions\OtpException;
use App\Exceptions\TransactionException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (PemdaVerificationException $e, $request) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        });

        $exceptions->render(function (OtpException $e, $request) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
        });

        $exceptions->render(function (TransactionException $e, $request) {
    return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 422);
});
    })->create();
