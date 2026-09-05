@extends('layouts.admin')

@section('title', $supplier->name)
@section('heading', $supplier->name)
@section('subtitle', 'Supplier details, vehicles and delivery history')

@section('topbar')
    <a href="{{ route('admin.material-receipts.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary btn-sm">+ Record delivery</a>
    <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-outline btn-sm">Edit</a>
    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline btn-sm">Back to suppliers</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="form-grid">
                <div class="field">
                    <label>Contact person</label>
                    <input type="text" value="{{ $supplier->contact_person ?: '—' }}" readonly>
                </div>
                <div class="field">
                    <label>Phone</label>
                    <input type="text" value="{{ $supplier->phone ?: '—' }}" readonly>
                </div>
                <div class="field">
                    <label>Alt. phone</label>
                    <input type="text" value="{{ $supplier->alt_phone ?: '—' }}" readonly>
                </div>
                <div class="field">
                    <label>Email</label>
                    <input type="text" value="{{ $supplier->email ?: '—' }}" readonly>
                </div>
                <div class="field">
                    <label>Status</label>
                    <input type="text" value="{{ $supplier->is_active ? 'Active' : 'Inactive' }}" readonly>
                </div>
                <div class="field full">
                    <label>Address</label>
                    <textarea readonly>{{ $supplier->address ?: '—' }}</textarea>
                </div>
                @if ($supplier->notes)
                    <div class="field full">
                        <label>Notes</label>
                        <textarea readonly>{{ $supplier->notes }}</textarea>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Vehicles</h2>
            <a href="{{ route('admin.vehicles.create', ['supplier' => $supplier->id]) }}" class="btn btn-outline btn-sm">+ Add vehicle</a>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Vehicle number</th>
                        <th>Driver</th>
                        <th>Driver phone</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplier->vehicles as $vehicle)
                        <tr>
                            <td class="primary-cell">{{ $vehicle->vehicle_number }}</td>
                            <td class="sub-cell">{{ $vehicle->driver_name ?: '—' }}</td>
                            <td class="sub-cell">{{ $vehicle->driver_phone ?: '—' }}</td>
                            <td>
                                @if ($vehicle->is_active)
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
                                    <p>No vehicles yet.</p>
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
            <h2>Raw materials supplied</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th class="num">Bill qty</th>
                        <th class="num">Received</th>
                        <th class="num">Damaged</th>
                        <th class="num">Accepted</th>
                        <th class="num">Pending</th>
                        <th>Last delivery</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materialSummary as $row)
                        <tr>
                            <td class="primary-cell">
                                <a href="{{ route('admin.materials.edit', $row['material']) }}">{{ $row['material']->name }}</a>
                            </td>
                            <td class="num sub-cell">{{ rtrim(rtrim(number_format($row['bill_qty'], 3, '.', ''), '0'), '.') }}</td>
                            <td class="num sub-cell">{{ rtrim(rtrim(number_format($row['received_qty'], 3, '.', ''), '0'), '.') }}</td>
                            <td class="num sub-cell">{{ rtrim(rtrim(number_format($row['damaged_qty'], 3, '.', ''), '0'), '.') }}</td>
                            <td class="num">
                                {{ rtrim(rtrim(number_format($row['accepted_qty'], 3, '.', ''), '0'), '.') }}
                                <span class="sub-cell">{{ $row['material']->unit_label }}</span>
                            </td>
                            <td class="num sub-cell">{{ rtrim(rtrim(number_format($row['pending_qty'], 3, '.', ''), '0'), '.') }}</td>
                            <td class="sub-cell">{{ $row['last_delivery']?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <p>No deliveries recorded yet.</p>
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
            <h2>Recent deliveries</h2>
            <a href="{{ route('admin.material-receipts.index', ['supplier' => $supplier->id]) }}" class="btn btn-outline btn-sm">View all ({{ $receiptCount }})</a>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Receipt no</th>
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
                            <td class="primary-cell">{{ $receipt->receipt_no }}</td>
                            <td class="sub-cell">{{ $receipt->bill_number ?: '—' }}</td>
                            <td class="sub-cell">{{ $receipt->received_date->format('d M Y') }}</td>
                            <td class="num">{{ $receipt->items_count }}</td>
                            <td><span class="badge {{ $receipt->status->color() }}">{{ $receipt->status->label() }}</span></td>
                            <td class="actions">
                                <a href="{{ route('admin.material-receipts.show', $receipt) }}" class="btn btn-outline btn-sm">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <p>No deliveries yet.</p>
                                    <p style="margin-top:12px">
                                        <a href="{{ route('admin.material-receipts.create', ['supplier' => $supplier->id]) }}" class="btn btn-primary btn-sm">Record a delivery</a>
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
