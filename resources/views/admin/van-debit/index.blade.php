@extends('layouts.admin')

@section('title', 'Van Debit Ledger')
@section('heading', 'Van Debit Ledger')
@section('subtitle', 'What each van currently owes, and the history behind it')

@section('topbar')
    <a href="{{ route('admin.van-debit.create') }}" class="btn btn-primary btn-sm">+ Add adjustment</a>
@endsection

@section('content')

    <div class="stat-grid">
        @foreach ($vans as $van)
            <div class="stat {{ (float) $van->current_debit_balance > 0 ? 'warn' : '' }}">
                <p class="label">{{ $van->name }}</p>
                <p class="value">Rs. {{ number_format($van->current_debit_balance, 2) }}</p>
                <p class="meta">outstanding debit</p>
            </div>
        @endforeach
    </div>

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
                    <a href="{{ route('admin.van-debit.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $transactions->total() }} entr{{ $transactions->total() === 1 ? 'y' : 'ies' }}</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Van</th>
                        <th class="num">Amount</th>
                        <th class="num">Balance after</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $txn)
                        <tr>
                            <td class="sub-cell">{{ $txn->transaction_date->format('d M Y') }}</td>
                            <td class="primary-cell">{{ $txn->van->name }}</td>
                            <td class="num">
                                @if ($txn->isIncrease())
                                    <span class="badge amber">+Rs. {{ number_format($txn->amount, 2) }}</span>
                                @else
                                    <span class="badge green">-Rs. {{ number_format(abs($txn->amount), 2) }}</span>
                                @endif
                            </td>
                            <td class="num sub-cell">Rs. {{ number_format($txn->balance_after, 2) }}</td>
                            <td class="sub-cell">{{ $txn->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <p class="big">▥</p>
                                    <p>No debit activity yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($transactions->hasPages())
            <div class="pagination-wrap">{{ $transactions->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
