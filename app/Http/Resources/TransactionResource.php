<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_transactions' => $this->id_transactions,
            'transaction_ref' => $this->transaction_ref,
            'tax_type' => $this->tax_type->value,
            'tax_type_label' => $this->tax_type->label(),
            'amount' => (float) $this->amount,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'proof_url' => $this->proof_url,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'bill' => new BillResource($this->whenLoaded('bill')),
            'payment_method' => new PaymentResource($this->whenLoaded('payment')),
            'object_name' => $this->whenLoaded('reference', fn() => $this->reference?->object_name ?? $this->reference?->business_name),
            'bank_code' => $this->bank_code?->value,
            'bank_label' => $this->bank_code?->label(),
            'va_number' => $this->va_number,
            'va_expired_at' => $this->va_expired_at,
            'payment_channel' => $this->payment_channel->value,
            'payment_channel_label' => $this->payment_channel->label(),
            'qr_string' => $this->qr_string,
            'qr_image_url' => $this->qr_image_url,
            'qr_expired_at' => $this->qr_expired_at,
        ];
    }
}