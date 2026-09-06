<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureHasTaxObject
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user->nops()->exists() && ! $user->npwpd()->exists()) {
            return response()->json([
                'message' => 'Anda wajib mendaftarkan minimal satu NOP atau NPWPD sebelum melanjutkan.',
            ], 403);
        }

        return $next($request);
    }
}