<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\User;

class PaymentMethodService
{
    public function store(User $user, array $data): Payment
    {
        $isFirstMethod = ! $user->payments()->exists();

        $payment = $user->payments()->create([
            'type' => $data['type'],
            'provider' => $data['provider'],
            'is_default' => $data['is_default'] ?? $isFirstMethod,
        ]);

        if ($payment->is_default) {
            $this->setAsDefault($user, $payment);
        }

        return $payment;
    }

    public function setAsDefault(User $user, Payment $payment): void
    {
        $user->payments()->where('id_payment', '!=', $payment->id_payment)->update(['is_default' => false]);
        $payment->update(['is_default' => true]);
    }
}