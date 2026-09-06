<?php

namespace App\Models;

use App\Enums\PaymentChannel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_payment';

    protected $fillable = [
        'id_user',
        'type',
        'provider',
        'token',
        'masked_number',
        'is_default',
    ];

    protected $hidden = [
        'token',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'type' => PaymentChannel::class,
        ];
    }

    // ── Relationships ────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id_payment', 'id_payment');
    }
}
