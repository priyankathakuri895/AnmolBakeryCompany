<?php

namespace App\Services;

use App\Enums\StockTransactionType;
use App\Models\RawMaterial;
use App\Models\StockTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The only place stock is allowed to move.
 *
 * Every change writes a ledger row carrying the balance it produced, so the
 * number on the material can always be explained by the rows behind it.
 */
class StockPoster
{
    /**
     * @param  float  $quantity  Signed, in the material's counting unit. Positive adds stock.
     * @param  Model|null  $reference  What caused the movement (receipt item, stock check item...).
     */
    public function post(
        RawMaterial $material,
        StockTransactionType $type,
        float $quantity,
        ?string $date = null,
        ?Model $reference = null,
        ?string $notes = null,
        ?int $userId = null,
    ): StockTransaction {
        return DB::transaction(function () use ($material, $type, $quantity, $date, $reference, $notes, $userId) {
            // Re-read under a lock so two deliveries saved at once cannot
            // both compute their balance from the same starting number.
            $locked = RawMaterial::whereKey($material->getKey())->lockForUpdate()->firstOrFail();

            $balance = round((float) $locked->current_stock + $quantity, 3);

            $transaction = new StockTransaction([
                'raw_material_id' => $locked->id,
                'type' => $type,
                'quantity' => $quantity,
                'balance_after' => $balance,
                'unit_size' => $locked->unit_size,
                'base_quantity' => $locked->toBaseQty($quantity),
                'base_unit' => $locked->base_unit,
                'transaction_date' => $date ?? now()->toDateString(),
                'notes' => $notes,
                'user_id' => $userId,
            ]);

            if ($reference) {
                $transaction->reference()->associate($reference);
            }

            $transaction->save();

            $locked->forceFill(['current_stock' => $balance])->save();
            $material->setAttribute('current_stock', $balance);

            return $transaction;
        });
    }
}
