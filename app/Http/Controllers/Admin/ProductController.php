<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStockTransactionType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStockTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->trim().'%';

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)->orWhere('code', 'like', $search);
                });
            })
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->input('status') === 'low', fn ($q) => $q
                ->whereNotNull('reorder_level')
                ->whereColumn('current_stock', '<=', 'reorder_level'))
            ->orderBy('name')
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $opening = (float) ($data['opening_stock'] ?? 0);

        // Opening stock is both a balance and the first row of the ledger,
        // so the current stock can always be explained.
        DB::transaction(function () use ($data, $opening, $request) {
            $data['current_stock'] = $opening;

            $product = Product::create($data);

            if ($opening > 0) {
                ProductStockTransaction::create([
                    'product_id' => $product->id,
                    'type' => ProductStockTransactionType::Opening,
                    'quantity' => $opening,
                    'balance_after' => $opening,
                    'transaction_date' => now()->toDateString(),
                    'notes' => 'Opening stock recorded when the product was created.',
                    'user_id' => $request->user()?->id,
                ]);
            }
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product added.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);

        // Opening stock and current stock are ledger-owned: they change through
        // production, loading and returns, never by editing this form.
        unset($data['opening_stock']);

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->stockTransactions()->exists() || $product->stockCheckItems()->exists()) {
            return back()->with(
                'error',
                'This product has stock history, so it cannot be deleted. Mark it inactive instead.'
            );
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('products', 'name')->ignore($product?->id),
            ],
            'code' => [
                'nullable', 'string', 'max:50',
                Rule::unique('products', 'code')->ignore($product?->id),
            ],
            'unit_label' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0'],
            'opening_stock' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
