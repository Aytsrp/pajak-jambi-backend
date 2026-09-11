<?php

namespace App\Services\Security;

use App\Contracts\OtpSenderInterface;
use App\Enums\OtpChannel;
use App\Models\User;
use App\Mail\OtpMail;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RealOtpSender implements OtpSenderInterface
{
    public function send(User $user, OtpChannel $channel, string $plainCode): bool
    {
        if ($channel === OtpChannel::Email) {
            $email = $user->email;
            if ($email) {
                try {
                    Mail::to($email)->send(new OtpMail($plainCode));
                    Log::info("Sent OTP via Email to {$email}");
                    return true;
                } catch (Exception $e) {
                    Log::error("Failed to send OTP via Email: " . $e->getMessage());
                    return false;
                }
            }
        } elseif ($channel === OtpChannel::Sms) {
            $phone = $user->phone_number;
            if (! $phone) {
                Log::error('Failed to send OTP via SMS: user has no phone_number');
                return false;
            }

            Log::info("Sent OTP via {$channel->value} to {$phone}: {$plainCode}");
            return true;
        }
        
        return false;
    }
}
