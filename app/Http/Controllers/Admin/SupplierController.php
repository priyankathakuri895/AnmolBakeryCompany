<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialReceiptItem;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::query()
            ->withCount(['vehicles', 'receipts'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->trim().'%';

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('contact_person', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
            })
            ->when($request->input('status') === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->input('status') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('name')
            ->paginate(15);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('admin.suppliers.create', ['supplier' => new Supplier]);
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['vehicles' => fn ($q) => $q->orderBy('vehicle_number')]);

        $receipts = $supplier->receipts()
            ->withCount('items')
            ->orderByDesc('received_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $materialSummary = MaterialReceiptItem::query()
            ->whereHas('receipt', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->with(['rawMaterial', 'receipt'])
            ->get()
            ->groupBy('raw_material_id')
            ->map(fn ($group) => [
                'material' => $group->first()->rawMaterial,
                'bill_qty' => $group->sum(fn ($i) => (float) $i->bill_qty),
                'received_qty' => $group->sum(fn ($i) => (float) $i->received_qty),
                'damaged_qty' => $group->sum(fn ($i) => (float) $i->damaged_qty),
                'accepted_qty' => $group->sum(fn ($i) => (float) $i->accepted_qty),
                'pending_qty' => $group->sum(fn ($i) => (float) $i->pending_qty),
                'last_delivery' => $group->max(fn ($i) => $i->receipt->received_date),
            ])
            ->sortBy(fn ($row) => $row['material']->name)
            ->values();

        return view('admin.suppliers.show', [
            'supplier' => $supplier,
            'receipts' => $receipts,
            'receiptCount' => $supplier->receipts()->count(),
            'materialSummary' => $materialSummary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validated($request));

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier added.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validated($request, $supplier));

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        // Deliveries reference the supplier, so history would break.
        if ($supplier->receipts()->exists()) {
            return back()->with(
                'error',
                'This supplier has delivery records, so it cannot be deleted. Mark it inactive instead.'
            );
        }

        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted.');
    }

    private function validated(Request $request, ?Supplier $supplier = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable', 'string', 'max:50',
                Rule::unique('suppliers', 'code')->ignore($supplier?->id),
            ],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'alt_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
