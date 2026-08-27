<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\Security\AccountSecurityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        private readonly AccountSecurityService $accountSecurity,
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
}
