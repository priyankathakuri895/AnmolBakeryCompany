@extends('layouts.admin')

@section('title', 'Products')
@section('heading', 'Products')
@section('subtitle', 'The finished-goods catalog sold from the vans')

@section('topbar')
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">+ Add product</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" class="toolbar">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product name">
                <select name="status">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active only</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive only</option>
                    <option value="low" @selected(request('status') === 'low')>Low stock only</option>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if (request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="btn btn-danger btn-sm">Clear</a>
                @endif
            </form>
            <span class="hint">{{ $products->total() }} product(s)</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="num">Price</th>
                        <th class="num">Current stock</th>
                        <th class="num">Reorder level</th>
                        <th>Status</th>
                        <th class="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <span class="primary-cell">{{ $product->name }}</span>
                                @if ($product->code)
                                    <br><span class="sub-cell">{{ $product->code }}</span>
                                @endif
                            </td>
                            <td class="num sub-cell">Rs. {{ number_format($product->price, 2) }}</td>
                            <td class="num">
                                {{ rtrim(rtrim(number_format($product->current_stock, 3, '.', ''), '0'), '.') }}
                                <span class="sub-cell">{{ $product->unit_label }}</span>
                            </td>
                            <td class="num sub-cell">
                                {{ $product->reorder_level === null
                                    ? '—'
                                    : rtrim(rtrim(number_format($product->reorder_level, 3, '.', ''), '0'), '.') }}
                            </td>
                            <td>
                                @if (! $product->is_active)
                                    <span class="badge gray">Inactive</span>
                                @elseif ($product->isBelowReorderLevel())
                                    <span class="badge amber">Low stock</span>
                                @else
                                    <span class="badge green">OK</span>
                                @endif
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline btn-sm">Edit</a>
                                <form method="POST"
                                      action="{{ route('admin.products.destroy', $product) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Delete {{ $product->name }}?')">
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
                                    <p class="big">◆</p>
                                    <p>No products found.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Add a product</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="pagination-wrap">{{ $products->withQueryString()->links('vendor.pagination.anmol') }}</div>
        @endif
    </div>

@endsection
