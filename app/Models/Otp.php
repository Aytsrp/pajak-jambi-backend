<?php

namespace App\Models;

use App\Enums\OtpChannel;
use App\Enums\OtpPurpose;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Otp extends Model
{
    use HasFactory;

    protected $table = 'otps';
    protected $primaryKey = 'id_otp';

    protected $fillable = [
        'id_user',
        'purpose',
        'channel',
        'code_hash',
        'attempts',
        'expires_at',
        'used_at',
    ];

    protected $hidden = [
        'code_hash',
    ];

    protected function casts(): array
    {
        return [
            'purpose' => OtpPurpose::class,
            'channel' => OtpChannel::class,
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // ── Helpers ───────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isUsed();
    }

    public function matches(string $plainCode): bool
    {
        return Hash::check($plainCode, $this->code_hash);
    }
}