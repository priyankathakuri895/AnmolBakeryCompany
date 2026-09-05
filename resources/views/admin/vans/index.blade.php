@extends('layouts.admin')

@section('title', 'Vans')
@section('heading', 'Vans')
@section('subtitle', 'The delivery fleet that sells finished products on daily routes')

@section('topbar')
    <a href="{{ route('admin.vans.create') }}" class="btn btn-primary btn-sm">+ Add van</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search van name or registration">
                <select name="status">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active only</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive only</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.vans.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $vans->total() }} van(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Van</th>
                        <th>Registration</th>
                        <th>Default salesman</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vans as $van)
                        <tr>
                            <td class="primary-cell">{{ $van->name }}</td>
                            <td class="sub-cell">{{ $van->registration_number ?: '—' }}</td>
                            <td class="sub-cell">{{ $van->defaultSalesman?->name ?: '—' }}</td>
                            <td>
                                @if ($van->is_active)
                                    <span class="badge green">Active</span>
                                @else
                                    <span class="badge gray">Inactive</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.vans.edit', $van) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('admin.vans.destroy', $van) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete {{ $van->name }}?')">
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
                                    <p class="big">▤</p>
                                    <p>No vans found.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.vans.create') }}" class="btn btn-primary btn-sm">Add a van</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vans->hasPages())
            <div class="pagination-wrap">{{ $vans->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
