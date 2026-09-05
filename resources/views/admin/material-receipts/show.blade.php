@extends('layouts.admin')

@section('title', $receipt->receipt_no)
@section('heading', $receipt->receipt_no)
@section('subtitle', $receipt->receipt_type->label().' from '.$receipt->supplier->name)

@section('topbar')
    @if ($receipt->isPending())
        <a href="{{ route('admin.material-receipts.follow-up.create', $receipt) }}" class="btn btn-primary btn-sm">Record follow-up delivery</a>
    @endif
    <a href="{{ route('admin.material-receipts.index') }}" class="btn btn-outline btn-sm">Back to receiving</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="form-grid">
                <div class="field">
                    <label>Status</label>
                    <input type="text" value="{{ $receipt->status->label() }}" readonly>
                </div>
                <div class="field">
                    <label>Supplier</label>
                    <input type="text" value="{{ $receipt->supplier->name }}" readonly>
                </div>
                <div class="field">
                    <label>Vehicle</label>
                    <input type="text" value="{{ $receipt->vehicle->vehicle_number ?? '—' }}" readonly>
                </div>
                <div class="field">
                    <label>Driver</label>
                    <input type="text" value="{{ $receipt->driver_name ?: '—' }}" readonly>
                </div>
                <div class="field">
                    <label>Date received</label>
                    <input type="text" value="{{ $receipt->received_date->format('d M Y') }}" readonly>
                </div>
                <div class="field">
                    <label>Bill number</label>
                    <input type="text" value="{{ $receipt->bill_number ?: '—' }}" readonly>
                </div>
                <div class="field">
                    <label>Bill date</label>
                    <input type="text" value="{{ $receipt->bill_date?->format('d M Y') ?? '—' }}" readonly>
                </div>
                <div class="field">
                    <label>Bill filed?</label>
                    <input type="text" value="{{ $receipt->bill_stacked ? 'Stacked '.$receipt->bill_stacked_at->format('d M Y') : 'Not yet' }}" readonly>
                </div>

                @if ($receipt->parentReceipt)
                    <div class="field">
                        <label>Follow-up to</label>
                        <a href="{{ route('admin.material-receipts.show', $receipt->parentReceipt) }}" class="btn btn-outline btn-sm">{{ $receipt->parentReceipt->receipt_no }}</a>
                    </div>
                @endif

                @if ($receipt->notes)
                    <div class="field full">
                        <label>Notes</label>
                        <textarea readonly>{{ $receipt->notes }}</textarea>
                    </div>
                @endif
            </div>

            @if (! $receipt->bill_stacked)
                <form method="POST" action="{{ route('admin.material-receipts.stack-bill', $receipt) }}" style="margin-top:16px">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Mark bill as stacked</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Bill summary</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th class="num">S.N.</th>
                        <th>Product</th>
                        <th class="num">Qty</th>
                        <th>Unit</th>
                        <th class="num">Rate</th>
                        <th class="num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receipt->items as $item)
                        <tr>
                            <td class="num sub-cell">{{ $loop->iteration }}</td>
                            <td class="primary-cell">{{ $item->rawMaterial->name }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format($item->bill_qty, 3, '.', ''), '0'), '.') }}</td>
                            <td class="sub-cell">{{ $item->unit_label }}</td>
                            <td class="num sub-cell">Rs. {{ number_format($item->rate, 2) }}</td>
                            <td class="num">Rs. {{ number_format($item->amount(), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="num" style="text-align:right"><strong>Total</strong></td>
                        <td class="num"><strong>Rs. {{ number_format($receipt->items->sum(fn ($i) => $i->amount()), 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if ($receipt->billImageUrl())
        <div class="card">
            <div class="card-header">
                <h2>Bill photo</h2>
            </div>
            <div class="card-body">
                <a href="{{ $receipt->billImageUrl() }}" target="_blank" rel="noopener">
                    <img src="{{ $receipt->billImageUrl() }}" alt="Bill photo" style="max-width:100%;max-height:500px;border:1px solid var(--admin-border);border-radius:var(--radius)">
                </a>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2>Line items</h2>
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
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receipt->items as $item)
                        <tr>
                            <td class="primary-cell">{{ $item->rawMaterial->name }}</td>
                            <td class="num sub-cell">{{ rtrim(rtrim(number_format($item->bill_qty, 3, '.', ''), '0'), '.') }}</td>
                            <td class="num sub-cell">{{ rtrim(rtrim(number_format($item->received_qty, 3, '.', ''), '0'), '.') }}</td>
                            <td class="num sub-cell">{{ rtrim(rtrim(number_format($item->damaged_qty, 3, '.', ''), '0'), '.') }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format($item->accepted_qty, 3, '.', ''), '0'), '.') }}</td>
                            <td class="num sub-cell">{{ rtrim(rtrim(number_format($item->pending_qty, 3, '.', ''), '0'), '.') }}</td>
                            <td>
                                @if ((float) $item->pending_qty > 0)
                                    <span class="badge amber">Pending</span>
                                @else
                                    <span class="badge green">Complete</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($receipt->followUpReceipts->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h2>Follow-up deliveries</h2>
            </div>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Receipt no</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receipt->followUpReceipts as $followUp)
                            <tr>
                                <td class="primary-cell">
                                    <a href="{{ route('admin.material-receipts.show', $followUp) }}">{{ $followUp->receipt_no }}</a>
                                </td>
                                <td class="sub-cell">{{ $followUp->received_date->format('d M Y') }}</td>
                                <td><span class="badge {{ $followUp->status->color() }}">{{ $followUp->status->label() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection
