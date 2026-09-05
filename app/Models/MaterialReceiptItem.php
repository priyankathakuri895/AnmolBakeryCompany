<?php

namespace App\Models;

use App\Enums\ReceiptItemStatus;
use App\Enums\ReceiptLinkType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MaterialReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_receipt_id', 'raw_material_id', 'parent_item_id', 'link_type',
        'bill_qty', 'rate', 'received_qty', 'damaged_qty', 'accepted_qty', 'pending_qty',
        'unit_label', 'unit_size', 'base_unit', 'base_qty', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'bill_qty' => 'decimal:3',
            'rate' => 'decimal:2',
            'received_qty' => 'decimal:3',
            'damaged_qty' => 'decimal:3',
            'accepted_qty' => 'decimal:3',
            'pending_qty' => 'decimal:3',
            'unit_size' => 'decimal:3',
            'base_qty' => 'decimal:3',
            'link_type' => ReceiptLinkType::class,
            'status' => ReceiptItemStatus::class,
        ];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(MaterialReceipt::class, 'material_receipt_id');
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    /** The short/damaged line this one settles. */
    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(MaterialReceiptItem::class, 'parent_item_id');
    }

    /** Later lines that settled this one. */
    public function childItems(): HasMany
    {
        return $this->hasMany(MaterialReceiptItem::class, 'parent_item_id');
    }

    public function stockTransactions(): MorphMany
    {
        return $this->morphMany(StockTransaction::class, 'reference');
    }

    /**
     * Recalculate accepted / pending / base quantity.
     *
     * Accepted = received - damaged; only accepted quantity ever becomes stock.
     * Pending   = bill - received, less whatever follow-up deliveries have settled.
     *
     * The outstanding amount lives on the ORIGINAL line only. A follow-up line is
     * a delivery, not a debt, so it never carries pending quantity of its own —
     * otherwise the same shortfall would be counted twice.
     */
    public function recalculate(): static
    {
        $received = (float) $this->received_qty;
        $damaged = (float) $this->damaged_qty;
        $bill = (float) $this->bill_qty;

        $this->accepted_qty = max($received - $damaged, 0);
        $this->base_qty = round((float) $this->accepted_qty * (float) $this->unit_size, 3);

        if ($this->link_type !== ReceiptLinkType::Initial) {
            $this->pending_qty = 0;

            if ($this->status !== ReceiptItemStatus::Cancelled) {
                $this->status = ReceiptItemStatus::Complete;
            }

            return $this;
        }

        $settled = (float) $this->childItems()
            ->where('link_type', ReceiptLinkType::PendingFulfilment)
            ->sum('received_qty');

        $this->pending_qty = max($bill - $received - $settled, 0);

        if ($this->status !== ReceiptItemStatus::Cancelled) {
            $this->status = (float) $this->pending_qty > 0
                ? ReceiptItemStatus::Pending
                : ReceiptItemStatus::Complete;
        }

        return $this;
    }

    /** Copy the material's current packaging onto the line, so history stays correct. */
    public function applyUnitSnapshot(?RawMaterial $material = null): static
    {
        $material ??= $this->rawMaterial;

        if ($material) {
            $this->unit_label = $material->unit_label;
            $this->unit_size = $material->unit_size;
            $this->base_unit = $material->base_unit->value;
        }

        return $this;
    }

    /** How much of this line the supplier still owes. */
    public function outstanding(): float
    {
        return (float) $this->pending_qty;
    }

    /** What the bill charges for this line: billed quantity at the billed rate. */
    public function amount(): float
    {
        return round((float) $this->bill_qty * (float) $this->rate, 2);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', ReceiptItemStatus::Pending)
            ->where('pending_qty', '>', 0);
    }

    public function scopeDamaged(Builder $query): Builder
    {
        return $query->where('damaged_qty', '>', 0);
    }
}
