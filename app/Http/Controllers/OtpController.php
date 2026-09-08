<?php

namespace App\Http\Controllers;

use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;
use App\Exceptions\OtpException;
use App\Http\Requests\RequestOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\Security\AccountSecurityService;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class OtpController extends Controller
{
    public function __construct(
        private readonly AccountSecurityService $accountSecurity,
    ) {}

    #[OA\Post(
        path: "/api/otp/request",
        summary: "Minta kode OTP dikirim (unlock akun / reset password / reset PIN)",
        tags: ["OTP"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nik", "purpose", "channel"],
                properties: [
                    new OA\Property(property: "nik", type: "string", example: "1671010101010001"),
                    new OA\Property(property: "purpose", type: "string", enum: ["unlock_account", "reset_password", "reset_pin"], example: "reset_password"),
                    new OA\Property(property: "channel", type: "string", enum: ["email", "sms"], example: "email"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Kode OTP dikirim (di dummy: dicatat ke storage/logs/laravel.log)"),
            new OA\Response(response: 422, description: "NIK tidak ditemukan"),
            new OA\Response(response: 429, description: "Terlalu sering meminta OTP"),
        ]
    )]
    public function request(RequestOtpRequest $request)
    {
        $user = User::where('nik', $request->nik)->firstOrFail();
        $purpose = OtpPurpose::from($request->purpose);
        $channel = OtpChannel::from($request->channel);

        $generated = $this->accountSecurity->generateOtp($user, $purpose, $channel);

        $payload = [
            'message' => 'Kode OTP telah dikirim. Kode berlaku selama '
                . config('security.otp.expiry_minutes') . ' menit.',
        ];

        // Dummy/local saja: supaya Postman/Collection Runner bisa ambil kode tanpa buka laravel.log
        if (! app()->isProduction()) {
            $payload['otp_code'] = $generated['plain_code'];
        }

        return response()->json($payload);
    }

    #[OA\Post(
        path: "/api/otp/verify",
        summary: "Verifikasi kode OTP — sekaligus eksekusi unlock/reset password/reset PIN",
        tags: ["OTP"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["nik", "purpose", "code"],
                properties: [
                    new OA\Property(property: "nik", type: "string", example: "1671010101010001"),
                    new OA\Property(property: "purpose", type: "string", enum: ["unlock_account", "reset_password", "reset_pin"], example: "reset_password"),
                    new OA\Property(property: "code", type: "string", example: "123456"),
                    new OA\Property(property: "new_password", type: "string", nullable: true, example: "passwordBaru123", description: "Wajib diisi jika purpose = reset_password"),
                    new OA\Property(property: "new_password_confirmation", type: "string", nullable: true, example: "passwordBaru123"),
                    new OA\Property(property: "new_pin", type: "string", nullable: true, example: "654321", description: "Wajib diisi jika purpose = reset_pin"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "OTP valid, aksi berhasil dieksekusi"),
            new OA\Response(response: 422, description: "Kode salah / kedaluwarsa / terlalu banyak percobaan"),
        ]
    )]
    public function verify(VerifyOtpRequest $request)
    {
        $user = User::where('nik', $request->nik)->firstOrFail();
        $purpose = OtpPurpose::from($request->purpose);

        try {
            $this->accountSecurity->verifyOtp($user, $purpose, $request->code);
        } catch (OtpException $e) {
            throw ValidationException::withMessages([
                'code' => $e->getMessage(),
            ]);
        }

        match ($purpose) {
            OtpPurpose::ResetPassword => $user->update(['password' => $request->new_password]),
            OtpPurpose::ResetPin => $user->update(['pin_number' => $request->new_pin]),
            OtpPurpose::UnlockAccount => null,
        };

        return response()->json([
            'message' => match ($purpose) {
                OtpPurpose::UnlockAccount => 'Akun berhasil dibuka kembali. Silakan login.',
                OtpPurpose::ResetPassword => 'Password berhasil diubah. Silakan login dengan password baru.',
                OtpPurpose::ResetPin => 'PIN berhasil diubah.',
            },
        ]);
    }
}