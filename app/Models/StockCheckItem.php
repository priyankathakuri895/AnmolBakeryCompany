<?php

namespace App\Models;

use App\Enums\StockDifferenceReason;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StockCheckItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_check_id', 'raw_material_id', 'system_qty', 'physical_qty',
        'difference', 'reason', 'reason_note', 'adjusted',
    ];

    protected function casts(): array
    {
        return [
            'system_qty' => 'decimal:3',
            'physical_qty' => 'decimal:3',
            'difference' => 'decimal:3',
            'reason' => StockDifferenceReason::class,
            'adjusted' => 'boolean',
        ];
    }

    public function stockCheck(): BelongsTo
    {
        return $this->belongsTo(StockCheck::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function stockTransactions(): MorphMany
    {
        return $this->morphMany(StockTransaction::class, 'reference');
    }

    /** Physical minus system: negative means stock is short. */
    public function recalculate(): static
    {
        $this->difference = (float) $this->physical_qty - (float) $this->system_qty;

        return $this;
    }

    public function hasDifference(): bool
    {
        return (float) $this->difference != 0.0;
    }
}
