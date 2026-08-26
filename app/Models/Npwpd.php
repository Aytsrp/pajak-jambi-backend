<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Npwpd extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_npwpd';

    protected $fillable = [
        'id_user',
        'npwpd_number',
        'business_name',
        'business_type',
        'owner_name',
        'is_verified',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
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

    public function unpaidBills(): MorphMany
    {
        return $this->bills()->whereIn('status', ['unpaid', 'overdue']);
    }
}