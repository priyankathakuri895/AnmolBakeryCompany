<?php

namespace App\Models;

use App\Enums\ProductStockTransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductStockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'type', 'quantity', 'balance_after',
        'reference_type', 'reference_id', 'transaction_date', 'notes', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'balance_after' => 'decimal:3',
            'type' => ProductStockTransactionType::class,
            'transaction_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The van load, return, stock check, etc. that caused this movement. */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isInward(): bool
    {
        return (float) $this->quantity > 0;
    }

    public function scopeOfType(Builder $query, ProductStockTransactionType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }
}
