<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class VanLoad extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'van_id', 'salesman_id', 'load_date', 'notes', 'delete_reason',
    ];

    protected function casts(): array
    {
        return [
            'load_date' => 'date',
        ];
    }

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(Salesman::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VanLoadItem::class);
    }

    public function settlement(): HasOne
    {
        return $this->hasOne(VanSettlement::class);
    }

    public function totalValue(): float
    {
        return (float) $this->items->sum(fn (VanLoadItem $item) => $item->lineTotal());
    }
}
