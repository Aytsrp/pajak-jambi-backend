<?php

namespace App\Http\Resources;

use App\Enums\TaxComponent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_bills' => $this->id_bills,
            'tax_period' => $this->tax_period,
            'tax_component' => $this->tax_component,
            'tax_component_label' => $this->tax_component
                ? TaxComponent::from($this->tax_component)->label()
                : null,
            'amount_due' => (float) $this->amount_due,
            'penalty_amount' => (float) $this->penalty_amount,
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'due_date' => $this->due_date->toDateString(),
            'is_overdue' => $this->isOverdue(),
        ];
    }
}
