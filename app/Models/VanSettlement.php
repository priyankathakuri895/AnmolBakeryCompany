<?php

namespace App\Models;

use App\Enums\SettlementStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VanSettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'van_load_id', 'status', 'cash_collected', 'online_collected',
        'debit_collected', 'new_debit_given', 'previous_debit_balance',
        'settled_by', 'returns_posted_at', 'finalized_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'cash_collected' => 'decimal:2',
            'online_collected' => 'decimal:2',
            'debit_collected' => 'decimal:2',
            'new_debit_given' => 'decimal:2',
            'previous_debit_balance' => 'decimal:2',
            'status' => SettlementStatus::class,
            'returns_posted_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function vanLoad(): BelongsTo
    {
        return $this->belongsTo(VanLoad::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VanSettlementItem::class);
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function isDraft(): bool
    {
        return $this->status === SettlementStatus::Draft;
    }

    public function isPendingPayment(): bool
    {
        return $this->status === SettlementStatus::PendingPayment;
    }

    public function isFinalized(): bool
    {
        return $this->status === SettlementStatus::Finalized;
    }

    public function scopeFinalized(Builder $query): Builder
    {
        return $query->where('status', SettlementStatus::Finalized);
    }

    public function totalSalesValue(): float
    {
        return (float) $this->items->sum(fn (VanSettlementItem $item) => $item->lineTotal());
    }

    public function netDebitChange(): float
    {
        return (float) $this->new_debit_given - (float) $this->debit_collected;
    }

    public function newDebitBalance(): float
    {
        return (float) $this->previous_debit_balance + $this->netDebitChange();
    }

    /** Sales value minus (cash + online + net debit change) — should be zero when balanced. */
    public function reconciliationDifference(): float
    {
        $expected = (float) $this->cash_collected + (float) $this->online_collected + $this->netDebitChange();

        return round($this->totalSalesValue() - $expected, 2);
    }
}
