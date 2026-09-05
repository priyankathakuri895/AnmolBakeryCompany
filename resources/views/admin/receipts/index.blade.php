@extends('layouts.admin')

@section('title', 'Receiving')
@section('heading', 'Raw Material Receiving')
@section('subtitle', 'Every delivery, what arrived, and what is still owed')

@section('topbar')
    <a href="{{ route('admin.receipts.create') }}" class="btn btn-primary btn-sm">+ Record delivery</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Receipt or bill number">
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
                    <option value="unstacked" @selected(request('status') === 'unstacked')>Bill not stacked</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['search', 'supplier', 'status']))
                    <a href="{{ route('admin.receipts.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $receipts->total() }} delivery(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Supplier</th>
                        <th>Vehicle</th>
                        <th>Bill</th>
                        <th class="num">Lines</th>
                        <th>Bill stacked</th>
                        <th>Status</th>
                        <th class="actions"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                        <tr>
                            <td>
                                <a href="{{ route('admin.receipts.show', $receipt) }}" class="primary-cell">{{ $receipt->receipt_no }}</a>
                                <br><span class="sub-cell">{{ $receipt->received_date->format('d M Y') }}</span>
                                @if ($receipt->isFollowUp())
                                    <br><span class="badge gray">Follow-up</span>
                                @endif
                            </td>
                            <td class="sub-cell">{{ $receipt->supplier->name }}</td>
                            <td class="sub-cell">{{ $receipt->vehicle?->vehicle_number ?: '—' }}</td>
                            <td class="sub-cell">
                                {{ $receipt->bill_number ?: '—' }}
                                @if ($receipt->bill_date)
                                    <br>{{ $receipt->bill_date->format('d M Y') }}
                                @endif
                            </td>
                            <td class="num">{{ $receipt->items_count }}</td>
                            <td>
                                @if ($receipt->bill_stacked)
                                    <span class="badge green">✓ Filed</span>
                                @else
                                    <span class="badge gray">Not filed</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $receipt->status->color() }}">{{ $receipt->status->label() }}</span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-outline btn-sm">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <p class="big">↓</p>
                                    <p>No deliveries recorded yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.receipts.create') }}" class="btn btn-primary btn-sm">Record the first delivery</a>
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
