<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'unit_label', 'price',
        'opening_stock', 'current_stock', 'reorder_level', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'opening_stock' => 'decimal:3',
            'current_stock' => 'decimal:3',
            'reorder_level' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(ProductStockTransaction::class);
    }

    public function stockCheckItems(): HasMany
    {
        return $this->hasMany(ProductStockCheckItem::class);
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
