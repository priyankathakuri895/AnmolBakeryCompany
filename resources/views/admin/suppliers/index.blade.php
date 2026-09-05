@extends('layouts.admin')

@section('title', 'Suppliers')
@section('heading', 'Suppliers')
@section('subtitle', 'Wholesalers who deliver raw material')

@section('topbar')
    <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm">+ Add supplier</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, contact or phone">
                <select name="status">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active only</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive only</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $suppliers->total() }} supplier(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact person</th>
                        <th>Phone</th>
                        <th class="num">Vehicles</th>
                        <th class="num">Deliveries</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>
                                <span class="primary-cell">{{ $supplier->name }}</span>
                                @if ($supplier->code)
                                    <br><span class="sub-cell">{{ $supplier->code }}</span>
                                @endif
                            </td>
                            <td class="sub-cell">{{ $supplier->contact_person ?: '—' }}</td>
                            <td class="sub-cell">{{ $supplier->phone ?: '—' }}</td>
                            <td class="num">{{ $supplier->vehicles_count }}</td>
                            <td class="num">{{ $supplier->receipts_count }}</td>
                            <td>
                                @if ($supplier->is_active)
                                    <span class="badge green">Active</span>
                                @else
                                    <span class="badge gray">Inactive</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-outline btn-sm">Details</a>
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('admin.suppliers.destroy', $supplier) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete {{ $supplier->name }}? This cannot be undone.')">
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
                                    <p class="big">◎</p>
                                    <p>No suppliers found.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm">Add the first supplier</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="pagination-wrap">{{ $suppliers->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
