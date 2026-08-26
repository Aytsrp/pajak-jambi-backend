<?php

namespace App\Models;

use App\Enums\VerificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $table = 'verification_logs';
    protected $primaryKey = 'id_verification_logs';

    protected $fillable = [
        'id_user',
        'type',
        'value_checked',
        'is_found',
        'source',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'type' => VerificationType::class,
            'is_found' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}