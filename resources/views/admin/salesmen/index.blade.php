@extends('layouts.admin')

@section('title', 'Salesmen')
@section('heading', 'Salesmen')
@section('subtitle', 'Staff who drive the vans and sell to shops on their route')

@section('topbar')
    <a href="{{ route('admin.salesmen.create') }}" class="btn btn-primary btn-sm">+ Add salesman</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone">
                <select name="status">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active only</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive only</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.salesmen.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $salesmen->total() }} salesman/men</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th class="num">Vans assigned</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesmen as $salesman)
                        <tr>
                            <td class="primary-cell">{{ $salesman->name }}</td>
                            <td class="sub-cell">{{ $salesman->phone ?: '—' }}</td>
                            <td class="num">{{ $salesman->vans_count }}</td>
                            <td>
                                @if ($salesman->is_active)
                                    <span class="badge green">Active</span>
                                @else
                                    <span class="badge gray">Inactive</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.salesmen.edit', $salesman) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('admin.salesmen.destroy', $salesman) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete {{ $salesman->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <p class="big">☺</p>
                                    <p>No salesmen found.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.salesmen.create') }}" class="btn btn-primary btn-sm">Add a salesman</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($salesmen->hasPages())
            <div class="pagination-wrap">{{ $salesmen->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
