@extends('layouts.admin')

@section('title', 'Van Load')
@section('heading', $vanLoad->van->name.' — '.$vanLoad->load_date->format('d M Y'))
@section('subtitle', 'Driven by '.$vanLoad->salesman->name)

@section('topbar')
    @if (! $vanLoad->settlement)
        <form method="POST" action="{{ route('admin.van-settlements.store') }}" style="display:inline">
            @csrf
            <input type="hidden" name="van_load_id" value="{{ $vanLoad->id }}">
            <button type="submit" class="btn btn-primary btn-sm">Settle this van</button>
        </form>
    @else
        <a href="{{ route('admin.van-settlements.edit', $vanLoad->settlement) }}" class="btn btn-primary btn-sm">
            @if ($vanLoad->settlement->isDraft())
                Continue settlement
            @elseif ($vanLoad->settlement->isPendingPayment())
                Record payment
            @else
                View settlement
            @endif
        </a>
    @endif

    @if (! $vanLoad->settlement)
        <form method="POST" action="{{ route('admin.van-loads.destroy', $vanLoad) }}" id="delete-load-form">
            @csrf
            @method('DELETE')
            <input type="hidden" name="reason" id="delete-load-reason">
            <button type="button" class="btn btn-danger btn-sm" onclick="deleteVanLoad()">Delete this load</button>
        </form>
    @endif

    <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">Print bill</button>
    <a href="{{ route('admin.van-loads.index') }}" class="btn btn-outline btn-sm">Back to loads</a>
@endsection

@section('content')

    <div class="print-only">
        <h1>ANMOL</h1>
        <p class="bill-print-tagline">Bakery &amp; Van Distribution</p>

        <div class="bill-print-meta">
            <div><strong>Van Loading Slip</strong><br>Ref: LD-{{ str_pad($vanLoad->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div><strong>Van</strong><br>{{ $vanLoad->van->name }}</div>
            <div><strong>Salesman</strong><br>{{ $vanLoad->salesman->name }}</div>
            <div><strong>Date</strong><br>{{ $vanLoad->load_date->format('d M Y') }}</div>
        </div>

        <table class="bill-print-table">
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
                @foreach ($vanLoad->items as $item)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td>{{ $item->product->name }}</td>
                        <td class="num">{{ rtrim(rtrim(number_format($item->quantity, 3, '.', ''), '0'), '.') }}</td>
                        <td>{{ $item->product->unit_label }}</td>
                        <td class="num">Rs. {{ number_format($item->unit_price, 2) }}</td>
                        <td class="num">Rs. {{ number_format($item->lineTotal(), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="num">Total Amount</td>
                    <td class="num">Rs. {{ number_format($vanLoad->totalValue(), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="bill-print-signatures">
            <div>Salesman Signature</div>
            <div>Checked by Owner</div>
        </div>
    </div>

    <div class="card no-print">
        <div class="card-header">
            <h2>Loaded products</h2>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="num">Quantity</th>
                        <th class="num">Unit price</th>
                        <th class="num">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vanLoad->items as $item)
                        <tr>
                            <td class="primary-cell">{{ $item->product->name }}</td>
                            <td class="num">
                                {{ rtrim(rtrim(number_format($item->quantity, 3, '.', ''), '0'), '.') }}
                                <span class="sub-cell">{{ $item->product->unit_label }}</span>
                            </td>
                            <td class="num sub-cell">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="num">Rs. {{ number_format($item->lineTotal(), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="num" style="text-align:right"><strong>Total load value</strong></td>
                        <td class="num"><strong>Rs. {{ number_format($vanLoad->totalValue(), 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if ($vanLoad->notes)
        <div class="card no-print">
            <div class="card-body">
                <p>{{ $vanLoad->notes }}</p>
            </div>
        </div>
    @endif

    @if (! $vanLoad->settlement)
        <script>
        function deleteVanLoad() {
            const reason = window.prompt('Why are you deleting this load? This restores the loaded stock. (required)');
            if (!reason || !reason.trim()) {
                return;
            }
            document.getElementById('delete-load-reason').value = reason.trim();
            document.getElementById('delete-load-form').submit();
        }
        </script>
    @endif

@endsection
