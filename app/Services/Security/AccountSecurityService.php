<?php

namespace App\Services\Security;

use App\Contracts\OtpSenderInterface;
use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Exceptions\OtpException;

class AccountSecurityService
{
    public function __construct(
        private readonly OtpSenderInterface $otpSender,
    ) {}

    // ── Login attempt handling ──────────────────────────

    public function registerFailedLogin(User $user): void
    {
        $max = config('security.login.max_attempts');
        $user->login_attempts++;

        if ($user->login_attempts >= $max) {
            $user->login_locked_until = now()->addMinutes(config('security.login.lock_minutes'));
        }

        $user->save();
    }

    public function resetLoginAttempts(User $user): void
    {
        $user->update([
            'login_attempts' => 0,
            'login_locked_until' => null,
        ]);
    }

    // ── PIN attempt handling ────────────────────────────

    public function registerFailedPin(User $user): void
    {
        $max = config('security.pin.max_attempts');
        $user->pin_attempts++;

        if ($user->pin_attempts >= $max) {
            $user->pin_locked_until = now()->addMinutes(config('security.pin.lock_minutes'));
        }

        $user->save();
    }

    public function resetPinAttempts(User $user): void
    {
        $user->update([
            'pin_attempts' => 0,
            'pin_locked_until' => null,
        ]);
    }

    // ── OTP: generate & kirim ───────────────────────────

    public function generateOtp(User $user, OtpPurpose $purpose, OtpChannel $channel): Otp
    {
        // Invalidate OTP lama yang masih aktif untuk purpose yang sama
        // (cegah numpuk banyak kode aktif sekaligus)
        $user->otps()
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]); // paksa expired-used

        $plainCode = str_pad((string) random_int(0, 999999), config('security.otp.length'), '0', STR_PAD_LEFT);

        $otp = $user->otps()->create([
            'purpose' => $purpose,
            'channel' => $channel,
            'code_hash' => Hash::make($plainCode),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(config('security.otp.expiry_minutes')),
        ]);

        $this->otpSender->send($user, $channel, $plainCode);

        return $otp;
    }

    // ── OTP: verifikasi ──────────────────────────────────

    /**
     * @throws OtpException
     */
    public function verifyOtp(User $user, OtpPurpose $purpose, string $plainCode): Otp
    {
        $otp = $user->otps()
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->latest('id_otp')
            ->first();

        if (! $otp) {
            throw OtpException::notFound();
        }

        if ($otp->isExpired()) {
            throw OtpException::expired();
        }

        if ($otp->attempts >= config('security.otp.max_verify_attempts')) {
            throw OtpException::tooManyAttempts();
        }

        if (! $otp->matches($plainCode)) {
            $otp->increment('attempts');
            throw OtpException::invalidCode();
        }

        $otp->update(['used_at' => now()]);

        // Efek samping sesuai purpose
        match ($purpose) {
            OtpPurpose::UnlockAccount => $this->resetLoginAttempts($user),
            OtpPurpose::ResetPin => $this->resetPinAttempts($user),
            OtpPurpose::ResetPassword => null, // password diganti terpisah setelah OTP valid, di controller
        };

        return $otp;
    }
}