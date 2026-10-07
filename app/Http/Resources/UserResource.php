<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_user' => $this->id_user,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'is_nik_verified' => $this->is_nik_verified,
            'has_nop' => $this->nops()->exists(),
            'has_npwpd' => $this->npwpd()->exists(),
        ];
    }
}