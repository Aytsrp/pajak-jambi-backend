<?php

use App\Exceptions\PemdaVerificationException;
use App\Exceptions\OtpException;
use App\Exceptions\TransactionException;
use App\Http\Middleware\EnsureHasTaxObject;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'has.tax.object' => EnsureHasTaxObject::class
            ,
        ]);

        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        // API-only: jangan redirect ke named route "login" (tidak ada) — itu yang bikin 500.
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

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
