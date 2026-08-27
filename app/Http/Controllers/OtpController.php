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

class OtpController extends Controller
{
    public function __construct(
        private readonly AccountSecurityService $accountSecurity,
    ) {}

    public function request(RequestOtpRequest $request)
    {
        $user = User::where('nik', $request->nik)->firstOrFail();
        $purpose = OtpPurpose::from($request->purpose);
        $channel = OtpChannel::from($request->channel);

        $this->accountSecurity->generateOtp($user, $purpose, $channel);

        // Pesan sengaja generik, tidak sebut "berhasil ke email X" secara detail
        // untuk hindari kebocoran info kontak user ke pihak yang tidak berhak.
        return response()->json([
            'message' => 'Kode OTP telah dikirim. Kode berlaku selama '
                . config('security.otp.expiry_minutes') . ' menit.',
        ]);
    }

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
            OtpPurpose::UnlockAccount => null, // sudah di-handle di dalam verifyOtp()
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