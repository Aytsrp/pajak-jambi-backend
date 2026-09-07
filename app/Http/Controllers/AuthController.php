<?php

namespace App\Http\Controllers;

use App\Exceptions\PemdaVerificationException;
use App\Exceptions\TransactionException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ChangePinRequest;
use App\Models\User;
use App\Services\Pemda\NikVerificationService;
use App\Services\Security\AccountSecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AccountSecurityService $accountSecurity,
        private readonly NikVerificationService $nikVerification,
    ) {}

    #[OA\Post(
        path: "/api/register",
        summary: "Registrasi akun baru",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nik", "full_name", "email", "phone_number", "password", "password_confirmation", "pin_number"],
                properties: [
                    new OA\Property(property: "nik", type: "string", example: "1671010101010001"),
                    new OA\Property(property: "full_name", type: "string", example: "Ahmad Fauzi"),
                    new OA\Property(property: "email", type: "string", example: "ahmad@example.com"),
                    new OA\Property(property: "phone_number", type: "string", example: "081234567890"),
                    new OA\Property(property: "password", type: "string", example: "password123"),
                    new OA\Property(property: "password_confirmation", type: "string", example: "password123"),
                    new OA\Property(property: "pin_number", type: "string", example: "123456"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Registrasi berhasil"),
            new OA\Response(response: 422, description: "Validasi gagal / NIK-email sudah dipakai"),
        ]
    )]

    public function register(RegisterRequest $request)
    {
        try {
            $this->nikVerification->verify($request->nik, $request->ip());
        } catch (PemdaVerificationException $e) {
            throw ValidationException::withMessages(['nik' => $e->getMessage()]);
        }

        $user = User::create(array_merge($request->validated(), [
            'is_nik_verified' => true,
        ]));

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan login.',
            'user' => [
                'id_user' => $user->id_user,
                'full_name' => $user->full_name,
                'email' => $user->email,
            ],
        ], 201);
    }

    #[OA\Post(
        path: "/api/login",
        summary: "Login menggunakan NIK & password",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nik", "password"],
                properties: [
                    new OA\Property(property: "nik", type: "string", example: "1671010101010001"),
                    new OA\Property(property: "password", type: "string", example: "password123"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Login berhasil, return token"),
            new OA\Response(response: 422, description: "NIK/password salah atau akun terkunci"),
        ]
    )]

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

    #[OA\Post(
        path: "/api/logout",
        summary: "Logout, hapus token aktif",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logout berhasil"),
        ]
    )]
    public function logout()
    {
        request()->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    #[OA\Post(
        path: "/api/change-password",
        summary: "Ubah password (user sudah login, konfirmasi pakai password lama)",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_password", "new_password", "new_password_confirmation"],
                properties: [
                    new OA\Property(property: "current_password", type: "string", example: "passwordLama123"),
                    new OA\Property(property: "new_password", type: "string", example: "passwordBaru456"),
                    new OA\Property(property: "new_password_confirmation", type: "string", example: "passwordBaru456"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Password berhasil diubah, token lain (device lain) otomatis logout"),
            new OA\Response(response: 422, description: "Password lama salah, atau password baru sama dengan lama"),
        ]
    )]
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password lama tidak sesuai.',
            ]);
        }

        $user->update(['password' => $request->new_password]);

        $currentTokenId = $request->user()->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return response()->json([
            'message' => 'Password berhasil diubah. Sesi login di perangkat lain telah dikeluarkan.',
        ]);
    }

    #[OA\Post(
        path: "/api/change-pin",
        summary: "Ubah PIN transaksi (user sudah login, konfirmasi pakai PIN lama)",
        tags: ["Auth"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["current_pin", "new_pin", "new_pin_confirmation"],
                properties: [
                    new OA\Property(property: "current_pin", type: "string", example: "123456"),
                    new OA\Property(property: "new_pin", type: "string", example: "654321"),
                    new OA\Property(property: "new_pin_confirmation", type: "string", example: "654321"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "PIN berhasil diubah"),
            new OA\Response(response: 422, description: "PIN lama salah, atau PIN baru sama dengan lama"),
            new OA\Response(response: 423, description: "PIN terkunci sementara karena terlalu banyak percobaan gagal — minta OTP reset PIN (purpose: reset_pin)"),
        ]
    )]
    public function changePin(ChangePinRequest $request)
    {
        $user = $request->user();

        if ($user->isPinLocked()) {
            throw TransactionException::pinLocked();
        }

        if (! Hash::check($request->current_pin, $user->pin_number)) {
            $this->accountSecurity->registerFailedPin($user);
            throw TransactionException::invalidPin();
        }

        $this->accountSecurity->resetPinAttempts($user);

        $user->update(['pin_number' => $request->new_pin]);

        return response()->json([
            'message' => 'PIN berhasil diubah.',
        ]);
    }
}
