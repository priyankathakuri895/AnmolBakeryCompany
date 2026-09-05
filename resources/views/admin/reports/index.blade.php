@extends('layouts.admin')

@section('title', 'Reports')
@section('heading', 'Reports')
@section('subtitle', 'Sales, expenses, debit and stock at a glance')

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="date" name="from" value="{{ $from->toDateString() }}">
                <input type="date" name="to" value="{{ $to->toDateString() }}">
                <button type="submit" class="btn btn-outline btn-sm">Apply range</button>
            </form>
            <span class="hint">{{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}</span>
        </div>
    </div>

    <div class="stat-grid">

        <div class="stat">
            <p class="label">Total Sales</p>
            <p class="value">Rs. {{ number_format($totalSales, 2) }}</p>
            <p class="meta">selected range</p>
        </div>

        <div class="stat">
            <p class="label">Total Expenses</p>
            <p class="value">Rs. {{ number_format($totalExpenses, 2) }}</p>
            <p class="meta">selected range</p>
        </div>

        <div class="stat {{ ($totalSales - $totalExpenses) < 0 ? 'warn' : '' }}">
            <p class="label">Net (Sales − Expenses)</p>
            <p class="value">Rs. {{ number_format($totalSales - $totalExpenses, 2) }}</p>
            <p class="meta">selected range</p>
        </div>

        <div class="stat {{ $totalDebitOutstanding > 0 ? 'warn' : '' }}">
            <p class="label">Debit Outstanding</p>
            <p class="value">Rs. {{ number_format($totalDebitOutstanding, 2) }}</p>
            <p class="meta">across all vans, as of today</p>
        </div>

        <div class="stat">
            <p class="label">Stock Value</p>
            <p class="value">Rs. {{ number_format($totalStockValue, 2) }}</p>
            <p class="meta">finished goods, as of today</p>
        </div>

    </div>

    <div class="card">
        <div class="card-header">
            <h2>Sales by Van</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Van</th>
                        <th class="num">Settlements</th>
                        <th class="num">Total Sales</th>
                        <th class="num">Cash</th>
                        <th class="num">Online</th>
                        <th class="num">Net Debit Change</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesByVan as $row)
                        <tr>
                            <td class="primary-cell">{{ $row['van'] }}</td>
                            <td class="num">{{ $row['settlements'] }}</td>
                            <td class="num">Rs. {{ number_format($row['total_sales'], 2) }}</td>
                            <td class="num sub-cell">Rs. {{ number_format($row['total_cash'], 2) }}</td>
                            <td class="num sub-cell">Rs. {{ number_format($row['total_online'], 2) }}</td>
                            <td class="num sub-cell">Rs. {{ number_format($row['net_debit_change'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <p>No finalized settlements in this range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Expenses by Category</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="num">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expensesByCategory as $row)
                        <tr>
                            <td class="primary-cell">{{ $row->category->label() }}</td>
                            <td class="num">Rs. {{ number_format($row->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">
                                <div class="empty-state">
                                    <p>No expenses in this range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Finished-Goods Stock Valuation</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="num">Current Stock</th>
                        <th class="num">Price</th>
                        <th class="num">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stockValuation as $row)
                        <tr>
                            <td class="primary-cell">{{ $row['product']->name }}</td>
                            <td class="num sub-cell">
                                {{ rtrim(rtrim(number_format($row['product']->current_stock, 3, '.', ''), '0'), '.') }}
                                {{ $row['product']->unit_label }}
                            </td>
                            <td class="num sub-cell">Rs. {{ number_format($row['product']->price, 2) }}</td>
                            <td class="num">Rs. {{ number_format($row['value'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
