<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\Security\AccountSecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AccountSecurityService $accountSecurity,
    ) {}

    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'token' => $token,
            'user' => [
                'id_user' => $user->id_user,
                'full_name' => $user->full_name,
                'email' => $user->email,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('nik', $request->nik)->first();

        if ($user && $user->isLoginLocked()) {
            throw ValidationException::withMessages([
                'nik' => 'Akun terkunci sementara karena terlalu banyak percobaan gagal. Silakan minta OTP unlock.',
            ]);
        }

        if (! $user || ! Auth::validate(['nik' => $request->nik, 'password' => $request->password])) {
            if ($user) {
                $this->accountSecurity->registerFailedLogin($user);
            }

            throw ValidationException::withMessages([
                'nik' => 'NIK atau password salah.',
            ]);
        }

        $this->accountSecurity->resetLoginAttempts($user);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id_user' => $user->id_user,
                'full_name' => $user->full_name,
                'has_nop' => $user->nops()->exists(),
                'has_npwpd' => $user->npwpd()->exists(),
            ],
        ]);
    }

    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}