@extends('layouts.admin')

@section('title', 'Van Loading')
@section('heading', 'Van Loading')
@section('subtitle', 'What each van took out for the day')

@section('topbar')
    <a href="{{ route('admin.van-loads.create') }}" class="btn btn-primary btn-sm">+ Load a van</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <select name="van">
                    <option value="">All vans</option>
                    @foreach ($vanOptions as $id => $name)
                        <option value="{{ $id }}" @selected((string) request('van') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['van']))
                    <a href="{{ route('admin.van-loads.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $loads->total() }} load(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Van</th>
                        <th>Salesman</th>
                        <th class="num">Products</th>
                        <th>Settlement</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loads as $load)
                        <tr>
                            <td class="sub-cell">{{ $load->load_date->format('d M Y') }}</td>
                            <td class="primary-cell">{{ $load->van->name }}</td>
                            <td class="sub-cell">{{ $load->salesman->name }}</td>
                            <td class="num">{{ $load->items_count }}</td>
                            <td>
                                @if (! $load->settlement)
                                    <span class="badge amber">Not settled</span>
                                @else
                                    <span class="badge {{ $load->settlement->status->color() }}">{{ $load->settlement->status->label() }}</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.van-loads.show', $load) }}" class="btn btn-outline btn-sm">View</a>
                                @if (! $load->settlement)
                                    <form method="POST" action="{{ route('admin.van-settlements.store') }}" style="display:inline">
                                        @csrf
                                        <input type="hidden" name="van_load_id" value="{{ $load->id }}">
                                        <button type="submit" class="btn btn-primary btn-sm">Settle</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.van-settlements.edit', $load->settlement) }}" class="btn btn-outline btn-sm">
                                        @if ($load->settlement->isDraft())
                                            Continue settlement
                                        @elseif ($load->settlement->isPendingPayment())
                                            Record payment
                                        @else
                                            View settlement
                                        @endif
                                    </a>
                                @endif
                                @if (! $load->settlement)
                                    <form method="POST" action="{{ route('admin.van-loads.destroy', $load) }}"
                                          id="delete-load-form-{{ $load->id }}" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="reason" id="delete-load-reason-{{ $load->id }}">
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteVanLoad({{ $load->id }})">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <p class="big">↓</p>
                                    <p>No vans loaded yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.van-loads.create') }}" class="btn btn-primary btn-sm">Load a van</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($loads->hasPages())
            <div class="pagination-wrap">{{ $loads->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

    <script>
    function deleteVanLoad(id) {
        const reason = window.prompt('Why are you deleting this load? This restores the loaded stock. (required)');
        if (!reason || !reason.trim()) {
            return;
        }
        document.getElementById('delete-load-reason-' + id).value = reason.trim();
        document.getElementById('delete-load-form-' + id).submit();
    }
    </script>

@endsection
