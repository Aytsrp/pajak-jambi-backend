<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_nop' => $this->id_nop,
            'nop_number' => $this->nop_number,
            'object_name' => $this->object_name,
            'owner_name' => $this->owner_name,
            'object_address' => $this->object_address,
            'is_verified' => $this->is_verified,
            'bills' => BillResource::collection($this->whenLoaded('bills')),
            'created_at' => $this->created_at,
        ];
    }
}