@extends('layouts.admin')

@section('title', 'Production Entries')
@section('heading', 'Production Entries')
@section('subtitle', 'Manual stock-in for finished goods produced by the factory')

@section('topbar')
    <a href="{{ route('admin.production.create') }}" class="btn btn-primary btn-sm">+ Record production</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <select name="product">
                    <option value="">All products</option>
                    @foreach ($productOptions as $id => $name)
                        <option value="{{ $id }}" @selected((string) request('product') === (string) $id)>{{ $name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['product']))
                    <a href="{{ route('admin.production.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $entries->total() }} entr{{ $entries->total() === 1 ? 'y' : 'ies' }}</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th class="num">Quantity</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="sub-cell">{{ $entry->transaction_date->format('d M Y') }}</td>
                            <td class="primary-cell">{{ $entry->product->name }}</td>
                            <td class="num">
                                +{{ rtrim(rtrim(number_format($entry->quantity, 3, '.', ''), '0'), '.') }}
                                <span class="sub-cell">{{ $entry->product->unit_label }}</span>
                            </td>
                            <td class="sub-cell">{{ $entry->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <p class="big">↓</p>
                                    <p>No production recorded yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.production.create') }}" class="btn btn-primary btn-sm">Record production</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($entries->hasPages())
            <div class="pagination-wrap">{{ $entries->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
