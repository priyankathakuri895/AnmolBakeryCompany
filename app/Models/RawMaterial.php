<?php

namespace App\Models;

use App\Enums\BaseUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'unit_label', 'unit_size', 'base_unit',
        'opening_stock', 'current_stock', 'reorder_level', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit_size' => 'decimal:3',
            'opening_stock' => 'decimal:3',
            'current_stock' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'base_unit' => BaseUnit::class,
            'is_active' => 'boolean',
        ];
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(MaterialReceiptItem::class);
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function stockCheckItems(): HasMany
    {
        return $this->hasMany(StockCheckItem::class);
    }

    /** Convert a quantity in counting units (packets/drums) to the base unit (kg/L/g). */
    public function toBaseQty(float|string $units): float
    {
        return round((float) $units * (float) $this->unit_size, 3);
    }

    /** Current stock expressed in the base unit, e.g. 35 packets -> 1750 kg. */
    public function getCurrentBaseStockAttribute(): float
    {
        return $this->toBaseQty($this->current_stock);
    }

    /** "50 kg / Packet" */
    public function getPackagingLabelAttribute(): string
    {
        return rtrim(rtrim(number_format((float) $this->unit_size, 3, '.', ''), '0'), '.')
            .' '.$this->base_unit->label().' / '.$this->unit_label;
    }

    public function isBelowReorderLevel(): bool
    {
        return $this->reorder_level !== null
            && (float) $this->current_stock <= (float) $this->reorder_level;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
