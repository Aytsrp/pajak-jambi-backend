<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_payment' => $this->id_payment,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'provider' => $this->provider,
            'is_default' => $this->is_default,
        ];
    }
}