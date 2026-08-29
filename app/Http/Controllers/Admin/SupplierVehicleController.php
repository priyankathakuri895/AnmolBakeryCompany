<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierVehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierVehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = SupplierVehicle::query()
            ->with('supplier')
            ->withCount('receipts')
            ->when($request->filled('supplier'), fn ($q) => $q->where('supplier_id', $request->integer('supplier')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->trim().'%';

                $query->where(function ($q) use ($search) {
                    $q->where('vehicle_number', 'like', $search)
                        ->orWhere('driver_name', 'like', $search)
                        ->orWhere('driver_phone', 'like', $search);
                });
            })
            ->orderBy('vehicle_number')
            ->paginate(15);

        return view('admin.vehicles.index', [
            'vehicles' => $vehicles,
            'supplierOptions' => $this->supplierOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.vehicles.create', [
            'vehicle' => new SupplierVehicle,
            'supplierOptions' => $this->supplierOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SupplierVehicle::create($this->validated($request));

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle added.');
    }

    public function edit(SupplierVehicle $vehicle): View
    {
        return view('admin.vehicles.edit', [
            'vehicle' => $vehicle,
            'supplierOptions' => $this->supplierOptions(),
        ]);
    }

    public function update(Request $request, SupplierVehicle $vehicle): RedirectResponse
    {
        $vehicle->update($this->validated($request, $vehicle));

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated.');
    }

    public function destroy(SupplierVehicle $vehicle): RedirectResponse
    {
        if ($vehicle->receipts()->exists()) {
            return back()->with(
                'error',
                'This vehicle appears on delivery records, so it cannot be deleted. Mark it inactive instead.'
            );
        }

        $vehicle->delete();

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted.');
    }

    private function validated(Request $request, ?SupplierVehicle $vehicle = null): array
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'vehicle_number' => [
                'required', 'string', 'max:50',
                Rule::unique('supplier_vehicles', 'vehicle_number')
                    ->where('supplier_id', $request->integer('supplier_id'))
                    ->ignore($vehicle?->id),
            ],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'driver_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ], [
            'vehicle_number.unique' => 'This supplier already has a vehicle with that number.',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function supplierOptions()
    {
        return Supplier::orderBy('name')->pluck('name', 'id');
    }
}
