<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Van extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'registration_number', 'default_salesman_id',
        'current_debit_balance', 'notes', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'current_debit_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function defaultSalesman(): BelongsTo
    {
        return $this->belongsTo(Salesman::class, 'default_salesman_id');
    }

    public function vanLoads(): HasMany
    {
        return $this->hasMany(VanLoad::class);
    }

    public function debitTransactions(): HasMany
    {
        return $this->hasMany(VanDebitTransaction::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
