@extends('layouts.admin')

@section('title', $receipt->receipt_no)
@section('heading', $receipt->receipt_no)
@section('subtitle', $receipt->supplier->name.' — received '.$receipt->received_date->format('d M Y'))

@section('topbar')
    @if ($receipt->isPending() && ! $receipt->isFollowUp())
        <a href="{{ route('admin.receipts.follow-up.create', $receipt) }}" class="btn btn-primary btn-sm">Record follow-up delivery</a>
    @endif

    <form method="POST" action="{{ route('admin.receipts.stack-bill', $receipt) }}">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-outline btn-sm">
            {{ $receipt->bill_stacked ? 'Un-mark bill stacked' : 'Mark bill stacked' }}
        </button>
    </form>
@endsection

@section('content')

    @if ($receipt->parentReceipt)
        <div class="alert success">
            This is a follow-up delivery against
            <a href="{{ route('admin.receipts.show', $receipt->parentReceipt) }}"><strong>{{ $receipt->parentReceipt->receipt_no }}</strong></a>.
        </div>
    @endif

    <div class="detail-grid">

        <div class="card">
            <div class="card-header"><h2>Delivery</h2></div>
            <div class="card-body">
                <dl class="detail-list">
                    <div><dt>Supplier</dt><dd>{{ $receipt->supplier->name }}</dd></div>
                    <div><dt>Vehicle</dt><dd>{{ $receipt->vehicle?->vehicle_number ?: '—' }}</dd></div>
                    <div><dt>Driver</dt><dd>{{ $receipt->driver_name ?: '—' }}</dd></div>
                    <div><dt>Received on</dt><dd>{{ $receipt->received_date->format('d M Y') }}</dd></div>
                    <div><dt>Bill number</dt><dd>{{ $receipt->bill_number ?: '—' }}</dd></div>
                    <div><dt>Bill date</dt><dd>{{ $receipt->bill_date?->format('d M Y') ?: '—' }}</dd></div>
                    <div>
                        <dt>Bill stacked</dt>
                        <dd>
                            @if ($receipt->bill_stacked)
                                <span class="badge green">✓ Filed</span>
                                <span class="sub-cell">{{ $receipt->bill_stacked_at?->format('d M Y') }}</span>
                            @else
                                <span class="badge gray">Not filed</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>Status</dt>
                        <dd><span class="badge {{ $receipt->status->color() }}">{{ $receipt->status->label() }}</span></dd>
                    </div>
                    <div><dt>Recorded by</dt><dd>{{ $receipt->receiver?->name ?: '—' }}</dd></div>
                    @if ($receipt->notes)
                        <div class="full"><dt>Notes</dt><dd>{{ $receipt->notes }}</dd></div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Bill</h2>
                @if ($receipt->bill_read_at)
                    <span class="hint">Read from photo {{ $receipt->bill_read_at->diffForHumans() }}</span>
                @endif
            </div>
            <div class="card-body">
                @if ($receipt->billImageUrl())
                    <a href="{{ $receipt->billImageUrl() }}" target="_blank" rel="noopener">
                        <img src="{{ $receipt->billImageUrl() }}" alt="Bill for {{ $receipt->receipt_no }}" class="bill-thumb">
                    </a>
                    <p class="sub-cell" style="margin-top:8px">Click to open the full image.</p>
                @else
                    <div class="empty-state" style="padding:24px">
                        <p class="big">🧾</p>
                        <p>No bill photo was attached to this delivery.</p>
                    </div>
                @endif
            </div>
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
                            <td class="num">{{ $item->bill_qty + 0 }}</td>
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

    <div class="card">
        <div class="card-header">
            <h2>Material lines</h2>
            <span class="hint">Accepted = received − damaged. Only accepted quantity entered stock.</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th class="num">Bill</th>
                        <th class="num">Received</th>
                        <th class="num">Damaged</th>
                        <th class="num">Accepted</th>
                        <th class="num">Pending</th>
                        <th class="num">Into stock</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($receipt->items as $item)
                        <tr>
                            <td>
                                <span class="primary-cell">{{ $item->rawMaterial->name }}</span>
                                <br><span class="sub-cell">{{ $item->unit_label }}</span>
                                @if ($item->link_type !== \App\Enums\ReceiptLinkType::Initial)
                                    <br><span class="badge gray">{{ $item->link_type->label() }}</span>
                                @endif
                            </td>
                            <td class="num">{{ $item->bill_qty + 0 }}</td>
                            <td class="num">{{ $item->received_qty + 0 }}</td>
                            <td class="num {{ (float) $item->damaged_qty > 0 ? 'flag-red' : '' }}">{{ $item->damaged_qty + 0 }}</td>
                            <td class="num"><strong>{{ $item->accepted_qty + 0 }}</strong></td>
                            <td class="num {{ (float) $item->pending_qty > 0 ? 'flag-amber' : '' }}">{{ $item->pending_qty + 0 }}</td>
                            <td class="num sub-cell">{{ number_format((float) $item->base_qty, 2) }} {{ $item->base_unit }}</td>
                            <td>
                                @if ($item->status === \App\Enums\ReceiptItemStatus::Pending)
                                    <span class="badge amber">Pending</span>
                                @else
                                    <span class="badge green">Complete</span>
                                @endif
                            </td>
                            <td class="sub-cell">
                                {{ $item->remarks ?: '—' }}
                                @if ($item->childItems->isNotEmpty())
                                    @foreach ($item->childItems as $child)
                                        <br><span class="sub-cell">
                                            +{{ $child->received_qty + 0 }} on
                                            <a href="{{ route('admin.receipts.show', $child->receipt) }}">{{ $child->receipt->receipt_no }}</a>
                                        </span>
                                    @endforeach
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
                <span class="hint">Material that was short and arrived later</span>
            </div>

            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Received on</th>
                            <th>What arrived</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receipt->followUpReceipts as $followUp)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.receipts.show', $followUp) }}" class="primary-cell">{{ $followUp->receipt_no }}</a>
                                </td>
                                <td class="sub-cell">{{ $followUp->received_date->format('d M Y') }}</td>
                                <td class="sub-cell">
                                    @foreach ($followUp->items as $item)
                                        {{ $item->rawMaterial->name }} {{ $item->received_qty + 0 }} {{ $item->unit_label }}@if (! $loop->last), @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (filled($receipt->bill_extraction['warnings'] ?? null))
        <div class="card">
            <div class="card-header">
                <h2>What the bill reader flagged</h2>
                <span class="hint">Kept for reference — the saved numbers above are what staff confirmed</span>
            </div>
            <div class="card-body">
                <ul class="reader-warnings" style="display:block">
                    @foreach ($receipt->bill_extraction['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

@endsection
