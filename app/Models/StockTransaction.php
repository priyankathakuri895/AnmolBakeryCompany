<?php

namespace App\Models;

use App\Enums\BaseUnit;
use App\Enums\StockTransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'raw_material_id', 'type', 'quantity', 'balance_after',
        'unit_size', 'base_quantity', 'base_unit',
        'reference_type', 'reference_id', 'transaction_date', 'notes', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'balance_after' => 'decimal:3',
            'unit_size' => 'decimal:3',
            'base_quantity' => 'decimal:3',
            'type' => StockTransactionType::class,
            'base_unit' => BaseUnit::class,
            'transaction_date' => 'date',
        ];
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The receipt item, stock check item, etc. that caused this movement. */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isInward(): bool
    {
        return (float) $this->quantity > 0;
    }

    public function scopeOfType(Builder $query, StockTransactionType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForMaterial(Builder $query, int $rawMaterialId): Builder
    {
        return $query->where('raw_material_id', $rawMaterialId);
    }
}
