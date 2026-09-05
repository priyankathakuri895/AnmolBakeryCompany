<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStockTransactionType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStockTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductionEntryController extends Controller
{
    public function index(Request $request): View
    {
        $entries = ProductStockTransaction::query()
            ->with('product')
            ->ofType(ProductStockTransactionType::Production)
            ->when($request->filled('product'), fn ($q) => $q->where('product_id', $request->integer('product')))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.production.index', [
            'entries' => $entries,
            'productOptions' => $this->productOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.production.create', [
            'productOptions' => $this->productOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'transaction_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $product = Product::findOrFail($data['product_id']);
            $newBalance = (float) $product->current_stock + (float) $data['quantity'];

            ProductStockTransaction::create([
                'product_id' => $product->id,
                'type' => ProductStockTransactionType::Production,
                'quantity' => $data['quantity'],
                'balance_after' => $newBalance,
                'transaction_date' => $data['transaction_date'],
                'notes' => $data['notes'] ?? null,
                'user_id' => $request->user()?->id,
            ]);

            $product->update(['current_stock' => $newBalance]);
        });

        return redirect()
            ->route('admin.production.index')
            ->with('success', 'Production stock recorded.');
    }

    private function productOptions()
    {
        return Product::active()->orderBy('name')->pluck('name', 'id');
    }
}
