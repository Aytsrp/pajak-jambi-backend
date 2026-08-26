<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nik',
        'full_name',
        'email',
        'phone_number',
        'pin_number',
        'password',
        'is_nik_verified',
    ];

    protected $hidden = [
        'password',
        'pin_number',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'pin_number' => 'hashed',
            'is_nik_verified' => 'boolean',
            'email_verified_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────

    public function nops(): HasMany
    {
        return $this->hasMany(Nop::class, 'id_user', 'id_user');
    }

    public function npwpd(): HasOne
    {
        // 1 user hanya boleh 1 NPWPD
        return $this->hasOne(Npwpd::class, 'id_user', 'id_user');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'id_user', 'id_user');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'id_user', 'id_user');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class, 'id_user', 'id_user');
    }
}