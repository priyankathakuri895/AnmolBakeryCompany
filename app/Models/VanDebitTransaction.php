<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VanDebitTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'van_id', 'van_settlement_id', 'amount', 'balance_after',
        'transaction_date', 'notes', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function vanSettlement(): BelongsTo
    {
        return $this->belongsTo(VanSettlement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isIncrease(): bool
    {
        return (float) $this->amount > 0;
    }
}
