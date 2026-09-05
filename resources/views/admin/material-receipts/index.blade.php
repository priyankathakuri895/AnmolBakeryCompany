@extends('layouts.admin')

@section('title', 'Receiving')
@section('heading', 'Raw Material Receiving')
@section('subtitle', 'Deliveries checked in against the supplier\'s bill')

@section('topbar')
    <a href="{{ route('admin.material-receipts.create') }}" class="btn btn-primary btn-sm">+ Record delivery</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search receipt no or bill no">
                <select name="supplier">
                    <option value="">All suppliers</option>
                    @foreach ($supplierOptions as $id => $name)
                        <option value="{{ $id }}" @selected((string) request('supplier') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <select name="status">
                    <option value="">All</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending only</option>
                    <option value="complete" @selected(request('status') === 'complete')>Complete only</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['search', 'supplier', 'status']))
                    <a href="{{ route('admin.material-receipts.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $receipts->total() }} receipt(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Receipt no</th>
                        <th>Supplier</th>
                        <th>Bill no</th>
                        <th>Date</th>
                        <th class="num">Lines</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                        <tr>
                            <td class="primary-cell">
                                {{ $receipt->receipt_no }}
                                @if ($receipt->receipt_type->value === 'follow_up')
                                    <br><span class="sub-cell">Follow-up</span>
                                @endif
                            </td>
                            <td class="sub-cell">{{ $receipt->supplier->name }}</td>
                            <td class="sub-cell">{{ $receipt->bill_number ?: '—' }}</td>
                            <td class="sub-cell">{{ $receipt->received_date->format('d M Y') }}</td>
                            <td class="num">{{ $receipt->items_count }}</td>
                            <td>
                                <span class="badge {{ $receipt->status->color() }}">{{ $receipt->status->label() }}</span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.material-receipts.show', $receipt) }}" class="btn btn-outline btn-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <p class="big">↓</p>
                                    <p>No deliveries recorded yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.material-receipts.create') }}" class="btn btn-primary btn-sm">Record a delivery</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($receipts->hasPages())
            <div class="pagination-wrap">{{ $receipts->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
