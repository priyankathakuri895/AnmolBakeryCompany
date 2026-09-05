@extends('layouts.admin')

@section('title', 'Expenses')
@section('heading', 'Expenses')
@section('subtitle', 'Fuel, maintenance, wastage and other business costs')

@section('topbar')
    <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm">+ Add expense</a>
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
                <select name="category">
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}" @selected(request('category') === $category->value)>{{ $category->label() }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}">
                <input type="date" name="to" value="{{ request('to') }}">
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['van', 'category', 'from', 'to']))
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $expenses->total() }} expense(s) — Rs. {{ number_format($totalAmount, 2) }}</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Van</th>
                        <th>Category</th>
                        <th class="num">Amount</th>
                        <th>Description</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="sub-cell">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td class="sub-cell">{{ $expense->van->name ?? 'General' }}</td>
                            <td><span class="badge gray">{{ $expense->category->label() }}</span></td>
                            <td class="num primary-cell">Rs. {{ number_format($expense->amount, 2) }}</td>
                            <td class="sub-cell">{{ $expense->description ?: '—' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('admin.expenses.destroy', $expense) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <p class="big">◔</p>
                                    <p>No expenses recorded yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary btn-sm">Add an expense</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($expenses->hasPages())
            <div class="pagination-wrap">{{ $expenses->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
