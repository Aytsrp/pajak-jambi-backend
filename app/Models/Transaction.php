
<?php

namespace App\Models;

use App\Enums\TaxType;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_transactions';

    protected $fillable = [
        'id_user',
        'id_bill',
        'id_payment',
        'transaction_ref',
        'tax_type',
        'reference_type',
        'reference_id',
        'amount',
        'gateway_ref',
        'status',
        'proof_url',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'tax_type' => TaxType::class,
            'status' => TransactionStatus::class,
        ];
    }

    // ── Relationships ────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'id_bill', 'id_bills');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'id_payment', 'id_payment');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Helpers ───────────────────────────────────────

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }
}
