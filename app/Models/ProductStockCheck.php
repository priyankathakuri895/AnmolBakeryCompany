<?php

namespace App\Models;

use App\Enums\StockCheckStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductStockCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'check_no', 'check_date', 'status', 'checked_by', 'finalized_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_date' => 'date',
            'finalized_at' => 'datetime',
            'status' => StockCheckStatus::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductStockCheckItem::class);
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function isDraft(): bool
    {
        return $this->status === StockCheckStatus::Draft;
    }

    /** Lines where physical count did not match the system. */
    public function discrepancies(): HasMany
    {
        return $this->items()->where('difference', '!=', 0);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', StockCheckStatus::Draft);
    }
}
