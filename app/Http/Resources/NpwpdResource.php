<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NpwpdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_npwpd' => $this->id_npwpd,
            'npwpd_number' => $this->npwpd_number,
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'owner_name' => $this->owner_name,
            'is_verified' => $this->is_verified,
            'bills' => BillResource::collection($this->whenLoaded('bills')),
            'created_at' => $this->created_at,
        ];
    }
}