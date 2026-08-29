<?php

namespace App\Models;

use App\Enums\ReceiptStatus;
use App\Enums\ReceiptType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no', 'supplier_id', 'supplier_vehicle_id', 'driver_name',
        'received_date', 'bill_number', 'bill_date', 'bill_stacked', 'bill_stacked_at',
        'receipt_type', 'parent_receipt_id', 'status', 'notes', 'received_by',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'bill_date' => 'date',
            'bill_stacked' => 'boolean',
            'bill_stacked_at' => 'datetime',
            'receipt_type' => ReceiptType::class,
            'status' => ReceiptStatus::class,
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(SupplierVehicle::class, 'supplier_vehicle_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaterialReceiptItem::class);
    }

    /** The receipt this one settles, when this is a follow-up delivery. */
    public function parentReceipt(): BelongsTo
    {
        return $this->belongsTo(MaterialReceipt::class, 'parent_receipt_id');
    }

    /** Later deliveries that settled pending or damaged quantity from this receipt. */
    public function followUpReceipts(): HasMany
    {
        return $this->hasMany(MaterialReceipt::class, 'parent_receipt_id');
    }

    public function isPending(): bool
    {
        return $this->status === ReceiptStatus::Pending;
    }

    /** Pending when any line still owes quantity. */
    public function resolveStatus(): ReceiptStatus
    {
        if ($this->status === ReceiptStatus::Cancelled) {
            return ReceiptStatus::Cancelled;
        }

        return $this->items->sum(fn ($item) => (float) $item->pending_qty) > 0
            ? ReceiptStatus::Pending
            : ReceiptStatus::Complete;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ReceiptStatus::Pending);
    }

    public function scopeBillNotStacked(Builder $query): Builder
    {
        return $query->where('bill_stacked', false);
    }
}
