<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStockTransactionType;
use App\Enums\SettlementStatus;
use App\Http\Controllers\Controller;
use App\Models\ProductStockTransaction;
use App\Models\VanDebitTransaction;
use App\Models\VanLoad;
use App\Models\VanSettlement;
use App\Models\VanSettlementItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VanSettlementController extends Controller
{
    public function index(Request $request): View
    {
        $settlements = VanSettlement::query()
            ->with(['vanLoad.van', 'vanLoad.salesman'])
            ->when($request->input('status') === 'draft', fn ($q) => $q->where('status', SettlementStatus::Draft))
            ->when($request->input('status') === 'pending_payment', fn ($q) => $q->where('status', SettlementStatus::PendingPayment))
            ->when($request->input('status') === 'finalized', fn ($q) => $q->where('status', SettlementStatus::Finalized))
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.van-settlements.index', compact('settlements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'van_load_id' => ['required', 'exists:van_loads,id'],
        ]);

        $vanLoad = VanLoad::with(['items', 'van'])->findOrFail($data['van_load_id']);

        if ($vanLoad->settlement()->exists()) {
            return back()->with('error', 'This load has already been settled.');
        }

        $settlement = DB::transaction(function () use ($vanLoad) {
            $settlement = VanSettlement::create([
                'van_load_id' => $vanLoad->id,
                'status' => SettlementStatus::Draft,
                'previous_debit_balance' => $vanLoad->van->current_debit_balance,
            ]);

            foreach ($vanLoad->items as $loadItem) {
                VanSettlementItem::create([
                    'van_settlement_id' => $settlement->id,
                    'van_load_item_id' => $loadItem->id,
                    'product_id' => $loadItem->product_id,
                    'qty_loaded' => $loadItem->quantity,
                    'unit_price' => $loadItem->unit_price,
                ]);
            }

            return $settlement;
        });

        return redirect()
            ->route('admin.van-settlements.edit', $settlement)
            ->with('success', 'Settlement started. Enter returns and collections, then finalize.');
    }

    public function edit(VanSettlement $settlement): View
    {
        $settlement->load(['items.product', 'vanLoad.van', 'vanLoad.salesman']);

        return view('admin.van-settlements.edit', compact('settlement'));
    }

    /** Step 1: enter/adjust returns while still a draft. Nothing posted yet. */
    public function update(Request $request, VanSettlement $settlement): RedirectResponse
    {
        if (! $settlement->isDraft()) {
            return back()->with('error', 'Returns have already been posted for this settlement.');
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.qty_returned_fresh' => ['required', 'numeric', 'min:0'],
            'items.*.qty_returned_expired' => ['required', 'numeric', 'min:0'],
        ]);

        // Validate every line before writing anything.
        foreach ($data['items'] as $itemId => $line) {
            $item = $settlement->items()->with('product')->find($itemId);

            if (! $item) {
                continue;
            }

            $returned = (float) $line['qty_returned_fresh'] + (float) $line['qty_returned_expired'];

            if ($returned > (float) $item->qty_loaded) {
                return back()->withInput()->with(
                    'error',
                    "Returned quantity for {$item->product->name} exceeds what was loaded."
                );
            }
        }

        DB::transaction(function () use ($data, $settlement) {
            $settlement->update(['notes' => $data['notes'] ?? null]);

            foreach ($data['items'] as $itemId => $line) {
                $item = $settlement->items()->whereKey($itemId)->first();

                if (! $item) {
                    continue;
                }

                $item->qty_returned_fresh = $line['qty_returned_fresh'];
                $item->qty_returned_expired = $line['qty_returned_expired'];
                $item->recalculate();
                $item->save();
            }
        });

        return redirect()
            ->route('admin.van-settlements.edit', $settlement)
            ->with('success', 'Returns saved. Post them once the counts are confirmed.');
    }

    /** Step 2: lock in the returns just counted — posts fresh stock back, computes the amount due. */
    public function postReturns(Request $request, VanSettlement $settlement): RedirectResponse
    {
        if (! $settlement->isDraft()) {
            return back()->with('error', 'Returns have already been posted for this settlement.');
        }

        DB::transaction(function () use ($request, $settlement) {
            $van = $settlement->vanLoad->van;

            foreach ($settlement->items()->with('product')->get() as $item) {
                if ((float) $item->qty_returned_fresh > 0) {
                    $product = $item->product;
                    $newBalance = (float) $product->current_stock + (float) $item->qty_returned_fresh;

                    ProductStockTransaction::create([
                        'product_id' => $product->id,
                        'type' => ProductStockTransactionType::VanReturn,
                        'quantity' => $item->qty_returned_fresh,
                        'balance_after' => $newBalance,
                        'reference_type' => VanSettlementItem::class,
                        'reference_id' => $item->id,
                        'transaction_date' => $settlement->vanLoad->load_date,
                        'notes' => "Unsold fresh stock returned from {$van->name}",
                        'user_id' => $request->user()?->id,
                    ]);

                    $product->update(['current_stock' => $newBalance]);
                }
            }

            $settlement->update([
                'status' => SettlementStatus::PendingPayment,
                'returns_posted_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.van-settlements.edit', $settlement)
            ->with('success', 'Returns posted. Amount due is set — record the payment when ready.');
    }

    /** Step 3: record how the amount due was actually paid, and settle the debit ledger. */
    public function finalize(Request $request, VanSettlement $settlement): RedirectResponse
    {
        if (! $settlement->isPendingPayment()) {
            return back()->with('error', 'Post the returns before recording payment.');
        }

        $data = $request->validate([
            'cash_collected' => ['required', 'numeric', 'min:0'],
            'online_collected' => ['required', 'numeric', 'min:0'],
            'debit_collected' => ['required', 'numeric', 'min:0'],
            'new_debit_given' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request, $data, $settlement) {
            $van = $settlement->vanLoad->van;

            $settlement->update([
                'cash_collected' => $data['cash_collected'],
                'online_collected' => $data['online_collected'],
                'debit_collected' => $data['debit_collected'],
                'new_debit_given' => $data['new_debit_given'],
            ]);

            $netDebitChange = (float) $settlement->new_debit_given - (float) $settlement->debit_collected;

            if ($netDebitChange != 0.0) {
                $newDebitBalance = (float) $van->current_debit_balance + $netDebitChange;

                VanDebitTransaction::create([
                    'van_id' => $van->id,
                    'van_settlement_id' => $settlement->id,
                    'amount' => $netDebitChange,
                    'balance_after' => $newDebitBalance,
                    'transaction_date' => $settlement->vanLoad->load_date,
                    'notes' => 'Net debit change from daily settlement',
                    'user_id' => $request->user()?->id,
                ]);

                $van->update(['current_debit_balance' => $newDebitBalance]);
            }

            $settlement->update([
                'status' => SettlementStatus::Finalized,
                'settled_by' => $request->user()?->id,
                'finalized_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.van-settlements.index')
            ->with('success', 'Settlement finalized. Stock and the debit ledger have been updated.');
    }

    public function destroy(VanSettlement $settlement): RedirectResponse
    {
        if (! $settlement->isDraft()) {
            return back()->with('error', 'A settlement cannot be deleted once returns have been posted.');
        }

        $settlement->delete();

        return redirect()
            ->route('admin.van-settlements.index')
            ->with('success', 'Settlement deleted.');
    }
}
