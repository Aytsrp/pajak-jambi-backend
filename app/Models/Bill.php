<?php

namespace App\Models;

use App\Enums\BillStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bill extends Model
{
    use HasFactory;

    protected $table = 'bills';
    protected $primaryKey = 'id_bills';

    protected $fillable = [
        'billable_type',
        'billable_id',
        'tax_period',
        'amount_due',
        'penalty_amount',
        'total_amount',
        'status',
        'due_date',
        'fetched_at',
    ];


    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'fetched_at' => 'datetime',
            'status' => BillStatus::class,
        ];
    }

    // ── Relationships ────────────────────────────────

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id_bill', 'id_bills');
    }

    // ── Helpers ───────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
