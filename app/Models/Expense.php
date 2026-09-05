<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_date', 'van_id', 'van_settlement_id', 'category', 'amount', 'description', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'category' => ExpenseCategory::class,
            'amount' => 'decimal:2',
        ];
    }

    public function van(): BelongsTo
    {
        return $this->belongsTo(Van::class);
    }

    public function vanSettlement(): BelongsTo
    {
        return $this->belongsTo(VanSettlement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
