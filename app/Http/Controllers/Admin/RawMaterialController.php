<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BaseUnit;
use App\Enums\StockTransactionType;
use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\StockTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RawMaterialController extends Controller
{
    public function index(Request $request): View
    {
        $materials = RawMaterial::query()
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

        return view('admin.materials.index', compact('materials'));
    }

    public function create(): View
    {
        return view('admin.materials.create', [
            'material' => new RawMaterial,
            'baseUnits' => BaseUnit::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $opening = (float) ($data['opening_stock'] ?? 0);

        // Opening stock is both a balance and the first row of the ledger,
        // so the current stock can always be explained.
        DB::transaction(function () use ($data, $opening) {
            $data['current_stock'] = $opening;

            $material = RawMaterial::create($data);

            if ($opening > 0) {
                StockTransaction::create([
                    'raw_material_id' => $material->id,
                    'type' => StockTransactionType::Opening,
                    'quantity' => $opening,
                    'balance_after' => $opening,
                    'unit_size' => $material->unit_size,
                    'base_quantity' => $material->toBaseQty($opening),
                    'base_unit' => $material->base_unit,
                    'transaction_date' => now()->toDateString(),
                    'notes' => 'Opening stock recorded when the material was created.',
                    'user_id' => $request->user()?->id,
                ]);
            }
        });

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Raw material added.');
    }

    public function edit(RawMaterial $material): View
    {
        return view('admin.materials.edit', [
            'material' => $material,
            'baseUnits' => BaseUnit::cases(),
        ]);
    }

    public function update(Request $request, RawMaterial $material): RedirectResponse
    {
        $data = $this->validated($request, $material);

        // Opening stock and current stock are ledger-owned: they change through
        // receiving and stock checks, never by editing this form.
        unset($data['opening_stock']);

        $material->update($data);

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Raw material updated.');
    }

    public function destroy(RawMaterial $material): RedirectResponse
    {
        if ($material->receiptItems()->exists() || $material->stockTransactions()->exists()) {
            return back()->with(
                'error',
                'This material has stock history, so it cannot be deleted. Mark it inactive instead.'
            );
        }

        $material->delete();

        return redirect()
            ->route('admin.materials.index')
            ->with('success', 'Raw material deleted.');
    }

    private function validated(Request $request, ?RawMaterial $material = null): array
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('raw_materials', 'name')->ignore($material?->id),
            ],
            'code' => [
                'nullable', 'string', 'max:50',
                Rule::unique('raw_materials', 'code')->ignore($material?->id),
            ],
            'unit_label' => ['required', 'string', 'max:50'],
            'unit_size' => ['required', 'numeric', 'min:0.001'],
            'base_unit' => ['required', Rule::enum(BaseUnit::class)],
            'opening_stock' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
