@extends('layouts.admin')

@section('title', 'Raw Materials')
@section('heading', 'Raw Materials')
@section('subtitle', 'Stock is counted in packets and drums; kg and litres are calculated')

@section('topbar')
    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary btn-sm">+ Add material</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search material name">
                <select name="status">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active only</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive only</option>
                    <option value="low" @selected(request('status') === 'low')>Low stock only</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $materials->total() }} material(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Packaging</th>
                        <th class="num">Current stock</th>
                        <th class="num">Base quantity</th>
                        <th class="num">Reorder level</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materials as $material)
                        <tr>
                            <td>
                                <span class="primary-cell">{{ $material->name }}</span>
                                @if ($material->code)
                                    <br><span class="sub-cell">{{ $material->code }}</span>
                                @endif
                            </td>
                            <td class="sub-cell">{{ $material->packaging_label }}</td>
                            <td class="num">
                                {{ rtrim(rtrim(number_format($material->current_stock, 3, '.', ''), '0'), '.') }}
                                <span class="sub-cell">{{ $material->unit_label }}</span>
                            </td>
                            <td class="num sub-cell">
                                {{ number_format($material->current_base_stock, 2) }} {{ $material->base_unit->label() }}
                            </td>
                            <td class="num sub-cell">
                                {{ $material->reorder_level === null
                                    ? '—'
                                    : rtrim(rtrim(number_format($material->reorder_level, 3, '.', ''), '0'), '.') }}
                            </td>
                            <td>
                                @if (! $material->is_active)
                                    <span class="badge gray">Inactive</span>
                                @elseif ($material->isBelowReorderLevel())
                                    <span class="badge amber">Low stock</span>
                                @else
                                    <span class="badge green">OK</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.materials.edit', $material) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('admin.materials.destroy', $material) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete {{ $material->name }}?')">
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
                                    <p class="big">◈</p>
                                    <p>No raw materials found.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.materials.create') }}" class="btn btn-primary btn-sm">Add a raw material</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($materials->hasPages())
            <div class="pagination-wrap">{{ $materials->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
