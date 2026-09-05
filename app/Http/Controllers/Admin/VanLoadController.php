<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStockTransactionType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStockTransaction;
use App\Models\Salesman;
use App\Models\Van;
use App\Models\VanLoad;
use App\Models\VanLoadItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VanLoadController extends Controller
{
    public function index(Request $request): View
    {
        $loads = VanLoad::query()
            ->with(['van', 'salesman', 'settlement'])
            ->withCount('items')
            ->when($request->filled('van'), fn ($q) => $q->where('van_id', $request->integer('van')))
            ->orderByDesc('load_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.van-loads.index', [
            'loads' => $loads,
            'vanOptions' => $this->vanOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.van-loads.create', [
            'vanOptions' => $this->vanOptions(),
            'salesmenOptions' => $this->salesmenOptions(),
            'products' => Product::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'van_id' => ['required', 'exists:vans,id'],
            'salesman_id' => ['required', 'exists:salesmen,id'],
            'load_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (VanLoad::where('van_id', $data['van_id'])->whereDate('load_date', $data['load_date'])->exists()) {
            return back()->withInput()->with('error', 'This van already has a load recorded for that date.');
        }

        $lines = collect($data['quantities'])->filter(fn ($qty) => (float) $qty > 0);

        if ($lines->isEmpty()) {
            return back()->withInput()->with('error', 'Load at least one product with a quantity greater than zero.');
        }

        $products = Product::whereIn('id', $lines->keys())->get()->keyBy('id');

        foreach ($lines as $productId => $qty) {
            $product = $products->get($productId);

            if (! $product || (float) $qty > (float) $product->current_stock) {
                return back()->withInput()->with(
                    'error',
                    "Cannot load {$qty} of {$product?->name}: only {$product?->current_stock} in stock."
                );
            }
        }

        $vanLoad = DB::transaction(function () use ($data, $lines, $products, $request) {
            $vanLoad = VanLoad::create([
                'van_id' => $data['van_id'],
                'salesman_id' => $data['salesman_id'],
                'load_date' => $data['load_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($lines as $productId => $qty) {
                $product = $products->get($productId);

                $item = VanLoadItem::create([
                    'van_load_id' => $vanLoad->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                ]);

                $newBalance = (float) $product->current_stock - (float) $qty;

                ProductStockTransaction::create([
                    'product_id' => $product->id,
                    'type' => ProductStockTransactionType::VanLoad,
                    'quantity' => -$qty,
                    'balance_after' => $newBalance,
                    'reference_type' => VanLoadItem::class,
                    'reference_id' => $item->id,
                    'transaction_date' => $data['load_date'],
                    'notes' => "Loaded onto {$vanLoad->van->name}",
                    'user_id' => $request->user()?->id,
                ]);

                $product->update(['current_stock' => $newBalance]);
            }

            return $vanLoad;
        });

        return redirect()
            ->route('admin.van-loads.show', $vanLoad)
            ->with('success', 'Van loaded.');
    }

    public function show(VanLoad $vanLoad): View
    {
        $vanLoad->load(['van', 'salesman', 'items.product', 'settlement']);

        return view('admin.van-loads.show', compact('vanLoad'));
    }

    public function destroy(Request $request, VanLoad $vanLoad): RedirectResponse
    {
        if ($vanLoad->settlement()->exists()) {
            return back()->with('error', 'This load has already been settled and cannot be deleted.');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($vanLoad, $data, $request) {
            foreach ($vanLoad->items()->with('product')->get() as $item) {
                $product = $item->product;
                $newBalance = (float) $product->current_stock + (float) $item->quantity;

                ProductStockTransaction::create([
                    'product_id' => $product->id,
                    'type' => ProductStockTransactionType::VanLoad,
                    'quantity' => $item->quantity,
                    'balance_after' => $newBalance,
                    'reference_type' => VanLoadItem::class,
                    'reference_id' => $item->id,
                    'transaction_date' => now()->toDateString(),
                    'notes' => "Load deleted: {$data['reason']}",
                    'user_id' => $request->user()?->id,
                ]);

                $product->update(['current_stock' => $newBalance]);
            }

            $vanLoad->update(['delete_reason' => $data['reason']]);
            $vanLoad->delete();
        });

        return redirect()
            ->route('admin.van-loads.index')
            ->with('success', 'Van load deleted and stock restored.');
    }

    private function vanOptions()
    {
        return Van::active()->orderBy('name')->pluck('name', 'id');
    }

    private function salesmenOptions()
    {
        return Salesman::active()->orderBy('name')->pluck('name', 'id');
    }
}
