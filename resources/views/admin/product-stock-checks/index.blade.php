@extends('layouts.admin')

@section('title', 'Stock Checks')
@section('heading', 'Finished-Goods Stock Checks')
@section('subtitle', 'Physical counts reconciled against the system stock ledger')

@section('topbar')
    <a href="{{ route('admin.product-stock-checks.create') }}" class="btn btn-primary btn-sm">+ Start stock check</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <select name="status">
                    <option value="">All</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft only</option>
                    <option value="finalized" @selected(request('status') === 'finalized')>Finalized only</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['status']))
                    <a href="{{ route('admin.product-stock-checks.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $checks->total() }} check(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Check no</th>
                        <th>Date</th>
                        <th class="num">Discrepancies</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($checks as $check)
                        <tr>
                            <td class="primary-cell">{{ $check->check_no }}</td>
                            <td class="sub-cell">{{ $check->check_date->format('d M Y') }}</td>
                            <td class="num">{{ $check->discrepancies_count }}</td>
                            <td>
                                @if ($check->isDraft())
                                    <span class="badge amber">Draft</span>
                                @else
                                    <span class="badge green">Finalized</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.product-stock-checks.edit', $check) }}" class="btn btn-outline btn-sm">
                                    {{ $check->isDraft() ? 'Continue' : 'View' }}
                                </a>
                                @if ($check->isDraft())
                                    <form method="POST"
                                          action="{{ route('admin.product-stock-checks.destroy', $check) }}"
                                          style="display:inline"
                                          onsubmit="return confirm('Delete stock check {{ $check->check_no }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <p class="big">✓</p>
                                    <p>No stock checks yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.product-stock-checks.create') }}" class="btn btn-primary btn-sm">Start a stock check</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($checks->hasPages())
            <div class="pagination-wrap">{{ $checks->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
