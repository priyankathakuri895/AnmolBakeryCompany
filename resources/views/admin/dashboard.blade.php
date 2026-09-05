@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subtitle', 'Van management and raw material stock at a glance')

@section('content')

    <div class="stat-grid">

        <div class="stat">
            <p class="label">Suppliers</p>
            <p class="value">{{ $supplierCount }}</p>
            <p class="meta">{{ $activeSupplierCount }} active</p>
        </div>

        <div class="stat">
            <p class="label">Supplier Vehicles</p>
            <p class="value">{{ $vehicleCount }}</p>
            <p class="meta">across all suppliers</p>
        </div>

        <div class="stat">
            <p class="label">Raw Materials</p>
            <p class="value">{{ $materialCount }}</p>
            <p class="meta">{{ $activeMaterialCount }} in use</p>
        </div>

        <div class="stat {{ $lowStockCount > 0 ? 'warn' : '' }}">
            <p class="label">Low Stock</p>
            <p class="value">{{ $lowStockCount }}</p>
            <p class="meta">at or below reorder level</p>
        </div>

        <div class="stat {{ $pendingReceiptCount > 0 ? 'warn' : '' }}">
            <p class="label">Pending Receipts</p>
            <p class="value">{{ $pendingReceiptCount }}</p>
            <p class="meta">material still owed by suppliers</p>
        </div>

        <div class="stat">
            <p class="label">Products</p>
            <p class="value">{{ $productCount }}</p>
            <p class="meta">{{ $activeProductCount }} active</p>
        </div>

        <div class="stat">
            <p class="label">Vans</p>
            <p class="value">{{ $vanCount }}</p>
            <p class="meta">{{ $activeVanCount }} active, {{ $salesmanCount }} salesmen</p>
        </div>

        <div class="stat {{ $lowStockProductCount > 0 ? 'warn' : '' }}">
            <p class="label">Low Stock Products</p>
            <p class="value">{{ $lowStockProductCount }}</p>
            <p class="meta">at or below reorder level</p>
        </div>

        <div class="stat {{ $openStockCheckCount > 0 ? 'warn' : '' }}">
            <p class="label">Open Stock Checks</p>
            <p class="value">{{ $openStockCheckCount }}</p>
            <p class="meta">draft, not yet finalized</p>
        </div>

    </div>

    <div class="card">
        <div class="card-header">
            <h2>Raw Material Stock</h2>
            <a href="{{ route('admin.materials.index') }}" class="btn btn-outline btn-sm">Manage materials</a>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Packaging</th>
                        <th class="num">Current Stock</th>
                        <th class="num">Base quantity</th>
                        <th class="num">Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materials as $material)
                        <tr>
                            <td class="primary-cell">{{ $material->name }}</td>
                            <td class="sub-cell">{{ $material->packaging_label }}</td>
                            <td class="num">
                                {{ rtrim(rtrim(number_format($material->current_stock, 3, '.', ''), '0'), '.') }}
                                <span class="sub-cell">{{ $material->unit_label }}</span>
                            </td>
                            <td class="num sub-cell">
                                {{ number_format($material->current_base_stock, 2) }} {{ $material->base_unit->label() }}
                            </td>
                            <td class="num sub-cell">
                                {{ $material->reorder_level === null
                                    ? '—'
                                    : rtrim(rtrim(number_format($material->reorder_level, 3, '.', ''), '0'), '.') }}
                            </td>
                            <td>
                                @if ($material->isBelowReorderLevel())
                                    <span class="badge amber">Low stock</span>
                                @else
                                    <span class="badge green">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <p class="big">◈</p>
                                    <p>No raw materials yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.materials.create') }}" class="btn btn-primary btn-sm">Add a raw material</a>
                                    </p>
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
            <h2>Suppliers &amp; their vehicles</h2>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline btn-sm">Manage suppliers</a>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Vehicles</th>
                        <th class="num">Deliveries</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="primary-cell">{{ $supplier->name }}</td>
                            <td class="sub-cell">
                                {{ $supplier->contact_person ?: '—' }}
                                @if ($supplier->phone)
                                    <br>{{ $supplier->phone }}
                                @endif
                            </td>
                            <td class="sub-cell">
                                @forelse ($supplier->vehicles as $vehicle)
                                    <span class="badge gray">{{ $vehicle->vehicle_number }}</span>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td class="num sub-cell">{{ $supplier->receipts_count }}</td>
                            <td>
                                @if ($supplier->is_active)
                                    <span class="badge green">Active</span>
                                @else
                                    <span class="badge gray">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <p class="big">◎</p>
                                    <p>No suppliers yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm">Add a supplier</a>
                                    </p>
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
            <h2>Finished Product Stock</h2>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">Manage products</a>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="num">Price</th>
                        <th class="num">Current Stock</th>
                        <th class="num">Reorder Level</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="primary-cell">{{ $product->name }}</td>
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
                                @if ($product->isBelowReorderLevel())
                                    <span class="badge amber">Low stock</span>
                                @else
                                    <span class="badge green">OK</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <p class="big">◆</p>
                                    <p>No products yet.</p>
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
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Vans &amp; their salesmen</h2>
            <a href="{{ route('admin.vans.index') }}" class="btn btn-outline btn-sm">Manage vans</a>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Van</th>
                        <th>Registration</th>
                        <th>Default salesman</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vans as $van)
                        <tr>
                            <td class="primary-cell">{{ $van->name }}</td>
                            <td class="sub-cell">{{ $van->registration_number ?: '—' }}</td>
                            <td class="sub-cell">{{ $van->defaultSalesman?->name ?: '—' }}</td>
                            <td>
                                @if ($van->is_active)
                                    <span class="badge green">Active</span>
                                @else
                                    <span class="badge gray">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <p class="big">▤</p>
                                    <p>No vans yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.vans.create') }}" class="btn btn-primary btn-sm">Add a van</a>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
