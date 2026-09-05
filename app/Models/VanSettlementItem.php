<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VanSettlementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'van_settlement_id', 'van_load_item_id', 'product_id',
        'qty_loaded', 'qty_returned_fresh', 'qty_returned_expired', 'qty_sold',
        'unit_price', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'qty_loaded' => 'decimal:3',
            'qty_returned_fresh' => 'decimal:3',
            'qty_returned_expired' => 'decimal:3',
            'qty_sold' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function vanSettlement(): BelongsTo
    {
        return $this->belongsTo(VanSettlement::class);
    }

    public function vanLoadItem(): BelongsTo
    {
        return $this->belongsTo(VanLoadItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockTransactions(): MorphMany
    {
        return $this->morphMany(ProductStockTransaction::class, 'reference');
    }

    /** loaded - returned fresh - returned expired, and the resulting line total. */
    public function recalculate(): static
    {
        $this->qty_sold = (float) $this->qty_loaded - (float) $this->qty_returned_fresh - (float) $this->qty_returned_expired;
        $this->line_total = round((float) $this->qty_sold * (float) $this->unit_price, 2);

        return $this;
    }

    public function lineTotal(): float
    {
        return (float) $this->line_total;
    }
}
