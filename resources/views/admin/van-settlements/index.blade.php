@extends('layouts.admin')

@section('title', 'Daily Settlements')
@section('heading', 'Daily Settlements')
@section('subtitle', "Each van's end-of-day reconciliation — returns, cash, online and debit")

@section('topbar')
    <a href="{{ route('admin.van-loads.index') }}" class="btn btn-outline btn-sm">Go to van loads</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <select name="status">
                    <option value="">All</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft (counting returns)</option>
                    <option value="pending_payment" @selected(request('status') === 'pending_payment')>Awaiting payment</option>
                    <option value="finalized" @selected(request('status') === 'finalized')>Finalized</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['status']))
                    <a href="{{ route('admin.van-settlements.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $settlements->total() }} settlement(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Van</th>
                        <th>Salesman</th>
                        <th class="num">Total sales</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settlements as $settlement)
                        <tr>
                            <td class="sub-cell">{{ $settlement->vanLoad->load_date->format('d M Y') }}</td>
                            <td class="primary-cell">{{ $settlement->vanLoad->van->name }}</td>
                            <td class="sub-cell">{{ $settlement->vanLoad->salesman->name }}</td>
                            <td class="num">Rs. {{ number_format($settlement->totalSalesValue(), 2) }}</td>
                            <td>
                                <span class="badge {{ $settlement->status->color() }}">{{ $settlement->status->label() }}</span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.van-settlements.edit', $settlement) }}" class="btn btn-outline btn-sm">
                                    @if ($settlement->isDraft())
                                        Continue
                                    @elseif ($settlement->isPendingPayment())
                                        Record payment
                                    @else
                                        View
                                    @endif
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <p class="big">✓</p>
                                    <p>No settlements yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.van-loads.index') }}" class="btn btn-primary btn-sm">Go to van loads</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($settlements->hasPages())
            <div class="pagination-wrap">{{ $settlements->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
