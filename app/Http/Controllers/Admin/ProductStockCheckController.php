<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStockTransactionType;
use App\Enums\StockCheckStatus;
use App\Enums\StockDifferenceReason;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStockCheck;
use App\Models\ProductStockCheckItem;
use App\Models\ProductStockTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductStockCheckController extends Controller
{
    public function index(Request $request): View
    {
        $checks = ProductStockCheck::query()
            ->withCount('discrepancies')
            ->when($request->input('status') === 'draft', fn ($q) => $q->where('status', StockCheckStatus::Draft))
            ->when($request->input('status') === 'finalized', fn ($q) => $q->where('status', StockCheckStatus::Finalized))
            ->orderByDesc('check_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.product-stock-checks.index', compact('checks'));
    }

    public function create(): View
    {
        return view('admin.product-stock-checks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'check_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $products = Product::active()->orderBy('name')->get();

        if ($products->isEmpty()) {
            return back()->with('error', 'Add an active product before starting a stock check.');
        }

        $check = DB::transaction(function () use ($data, $products, $request) {
            $check = ProductStockCheck::create([
                'check_no' => $this->nextCheckNo($data['check_date']),
                'check_date' => $data['check_date'],
                'status' => StockCheckStatus::Draft,
                'checked_by' => $request->user()?->id,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($products as $product) {
                ProductStockCheckItem::create([
                    'product_stock_check_id' => $check->id,
                    'product_id' => $product->id,
                    'system_qty' => $product->current_stock,
                    'physical_qty' => $product->current_stock,
                    'difference' => 0,
                ]);
            }

            return $check;
        });

        return redirect()
            ->route('admin.product-stock-checks.edit', $check)
            ->with('success', 'Stock check started. Enter the physical counts, then finalize.');
    }

    public function edit(ProductStockCheck $stockCheck): View
    {
        $stockCheck->load(['items.product']);

        return view('admin.product-stock-checks.edit', [
            'stockCheck' => $stockCheck,
            'reasons' => StockDifferenceReason::cases(),
        ]);
    }

    public function update(Request $request, ProductStockCheck $stockCheck): RedirectResponse
    {
        if (! $stockCheck->isDraft()) {
            return back()->with('error', 'This stock check is already finalized.');
        }

        $data = $request->validate([
            'check_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.physical_qty' => ['required', 'numeric', 'min:0'],
            'items.*.reason' => ['nullable', Rule::enum(StockDifferenceReason::class)],
            'items.*.reason_note' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $stockCheck) {
            $stockCheck->update([
                'check_date' => $data['check_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $itemId => $line) {
                $item = $stockCheck->items()->whereKey($itemId)->first();

                if (! $item) {
                    continue;
                }

                $item->physical_qty = $line['physical_qty'];
                $item->reason = $line['reason'] ?? null;
                $item->reason_note = $line['reason_note'] ?? null;
                $item->recalculate();
                $item->save();
            }
        });

        return redirect()
            ->route('admin.product-stock-checks.edit', $stockCheck)
            ->with('success', 'Counts saved. Finalize when they are confirmed.');
    }

    public function finalize(Request $request, ProductStockCheck $stockCheck): RedirectResponse
    {
        if (! $stockCheck->isDraft()) {
            return back()->with('error', 'This stock check is already finalized.');
        }

        DB::transaction(function () use ($request, $stockCheck) {
            foreach ($stockCheck->items()->with('product')->get() as $item) {
                if ($item->hasDifference()) {
                    $product = $item->product;
                    $newBalance = (float) $product->current_stock + (float) $item->difference;

                    ProductStockTransaction::create([
                        'product_id' => $product->id,
                        'type' => ProductStockTransactionType::Adjustment,
                        'quantity' => $item->difference,
                        'balance_after' => $newBalance,
                        'reference_type' => ProductStockCheckItem::class,
                        'reference_id' => $item->id,
                        'transaction_date' => $stockCheck->check_date,
                        'notes' => $item->reason?->label() ?? 'Stock check adjustment',
                        'user_id' => $request->user()?->id,
                    ]);

                    $product->update(['current_stock' => $newBalance]);
                }

                $item->update(['adjusted' => true]);
            }

            $stockCheck->update([
                'status' => StockCheckStatus::Finalized,
                'finalized_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.product-stock-checks.index')
            ->with('success', 'Stock check finalized. Adjustments have been posted to stock.');
    }

    public function destroy(ProductStockCheck $stockCheck): RedirectResponse
    {
        if (! $stockCheck->isDraft()) {
            return back()->with('error', 'A finalized stock check cannot be deleted.');
        }

        $stockCheck->delete();

        return redirect()
            ->route('admin.product-stock-checks.index')
            ->with('success', 'Stock check deleted.');
    }

    private function nextCheckNo(string $date): string
    {
        $seq = ProductStockCheck::whereDate('check_date', $date)->count() + 1;

        return 'PSC-'.str_replace('-', '', $date).'-'.str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
