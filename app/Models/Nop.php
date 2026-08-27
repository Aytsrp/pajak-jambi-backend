<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nop extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id_nop';

    protected $fillable = [
        'id_user',
        'nop_number',
        'object_name',
        'owner_name',
        'object_address',
        'is_verified',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function bills(): MorphMany
    {
        return $this->morphMany(Bill::class, 'billable');
    }

    // ── Helpers ───────────────────────────────────────

    public function unpaidBills(): MorphMany
    {
        return $this->bills()->whereIn('status', ['unpaid', 'overdue']);
    }
}