<?php

namespace App\Services\Dummy;

use App\Contracts\OtpSenderInterface;
use App\Enums\OtpChannel;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DummyOtpSender implements OtpSenderInterface
{
    public function send(User $user, OtpChannel $channel, string $plainCode): bool
    {
        // Dummy: tidak benar-benar kirim email/SMS, cuma dicatat ke log
        Log::info('[DummyOtpSender] OTP dikirim (simulasi)', [
            'id_user' => $user->id_user,
            'channel' => $channel->value,
            'target' => $channel === OtpChannel::Email ? $user->email : $user->phone_number,
            'code' => $plainCode, // AMAN karena cuma log lokal dummy, JANGAN pernah log OTP asli di production nanti
        ]);

        return true;
    }
}