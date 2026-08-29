@extends('layouts.admin')

@section('title', 'Supplier Vehicles')
@section('heading', 'Supplier Vehicles')
@section('subtitle', 'Vehicles belong to the suppliers — these are the vans that bring material in')

@section('topbar')
    <a href="{{ route('admin.vehicles.create', request()->only('supplier')) }}" class="btn btn-primary btn-sm">+ Add vehicle</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vehicle number or driver">
                <select name="supplier">
                    <option value="">All suppliers</option>
                    @foreach ($supplierOptions as $id => $name)
                        <option value="{{ $id }}" @selected((string) request('supplier') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['search', 'supplier']))
                    <a href="{{ route('admin.vehicles.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $vehicles->total() }} vehicle(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Vehicle number</th>
                        <th>Supplier</th>
                        <th>Driver</th>
                        <th>Driver phone</th>
                        <th class="num">Deliveries</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td class="primary-cell">{{ $vehicle->vehicle_number }}</td>
                            <td class="sub-cell">{{ $vehicle->supplier->name }}</td>
                            <td class="sub-cell">{{ $vehicle->driver_name ?: '—' }}</td>
                            <td class="sub-cell">{{ $vehicle->driver_phone ?: '—' }}</td>
                            <td class="num">{{ $vehicle->receipts_count }}</td>
                            <td>
                                @if ($vehicle->is_active)
                                    <span class="badge green">Active</span>
                                @else
                                    <span class="badge gray">Inactive</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('admin.vehicles.destroy', $vehicle) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete vehicle {{ $vehicle->vehicle_number }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <p class="big">▤</p>
                                    <p>No vehicles found.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary btn-sm">Add a vehicle</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vehicles->hasPages())
            <div class="pagination-wrap">{{ $vehicles->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
