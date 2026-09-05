@extends('layouts.admin')

@php($isDraft = $settlement->isDraft())
@php($isPendingPayment = $settlement->isPendingPayment())
@php($isFinalized = $settlement->isFinalized())

@section('title', 'Settlement — '.$settlement->vanLoad->van->name)
@section('heading', $settlement->vanLoad->van->name.' — '.$settlement->vanLoad->load_date->format('d M Y'))
@section('subtitle', 'Driven by '.$settlement->vanLoad->salesman->name)

@section('topbar')
    @unless ($isDraft)
        <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">Print bill</button>
    @endunless
    <a href="{{ route('admin.van-settlements.index') }}" class="btn btn-outline btn-sm">Back to settlements</a>
@endsection

@section('content')

    @unless ($isDraft)
        <div class="print-only">
            <h1>ANMOL</h1>
            <p class="bill-print-tagline">Bakery &amp; Van Distribution</p>

            <div class="bill-print-meta">
                <div><strong>Settlement Bill</strong><br>Ref: STL-{{ str_pad($settlement->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div><strong>Van</strong><br>{{ $settlement->vanLoad->van->name }}</div>
                <div><strong>Salesman</strong><br>{{ $settlement->vanLoad->salesman->name }}</div>
                <div><strong>Date</strong><br>{{ $settlement->vanLoad->load_date->format('d M Y') }}</div>
            </div>

            <table class="bill-print-table">
                <thead>
                    <tr>
                        <th class="num">S.N.</th>
                        <th>Product</th>
                        <th class="num">Qty sold</th>
                        <th>Unit</th>
                        <th class="num">Rate</th>
                        <th class="num">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settlement->items as $item)
                        <tr>
                            <td class="num">{{ $loop->iteration }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td class="num">{{ rtrim(rtrim(number_format($item->qty_sold, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ $item->product->unit_label }}</td>
                            <td class="num">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="num">Rs. {{ number_format($item->lineTotal(), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="num">Total Amount</td>
                        <td class="num">Rs. {{ number_format($settlement->totalSalesValue(), 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="bill-print-signatures">
                <div>Salesman Signature</div>
                <div>Checked by Owner</div>
            </div>
        </div>
    @endunless

    <div class="card no-print">
        <div class="card-body">
            <div class="form-grid">
                <div class="field">
                    <label>Status</label>
                    <input type="text" value="{{ $settlement->status->label() }}" readonly>
                </div>
                <div class="field">
                    <label>Previous debit balance</label>
                    <input type="text" value="Rs. {{ number_format($settlement->previous_debit_balance, 2) }}" readonly>
                </div>
                @unless ($isDraft)
                    <div class="field">
                        <label>Total amount due</label>
                        <input type="text" value="Rs. {{ number_format($settlement->totalSalesValue(), 2) }}" readonly>
                    </div>
                @endunless
            </div>
        </div>
    </div>

    {{-- Step 1: Returns — locked in by "Post returns" --}}
    <form method="POST" action="{{ route('admin.van-settlements.update', $settlement) }}" class="no-print">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h2>Returns</h2>
            </div>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="num">Loaded</th>
                            <th class="num">Returned fresh</th>
                            <th class="num">Returned expired</th>
                            <th class="num">Sold</th>
                            <th class="num">Line total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($settlement->items as $item)
                            <tr>
                                <td class="primary-cell">
                                    {{ $item->product->name }}
                                    <br><span class="sub-cell">{{ $item->product->unit_label }}</span>
                                </td>
                                <td class="num sub-cell">
                                    {{ rtrim(rtrim(number_format($item->qty_loaded, 3, '.', ''), '0'), '.') }}
                                </td>
                                <td class="num">
                                    @if ($isDraft)
                                        <input type="number" step="0.001" min="0" max="{{ $item->qty_loaded }}"
                                               name="items[{{ $item->id }}][qty_returned_fresh]"
                                               value="{{ old('items.'.$item->id.'.qty_returned_fresh', $item->qty_returned_fresh) }}">
                                    @else
                                        {{ rtrim(rtrim(number_format($item->qty_returned_fresh, 3, '.', ''), '0'), '.') }}
                                    @endif
                                </td>
                                <td class="num">
                                    @if ($isDraft)
                                        <input type="number" step="0.001" min="0" max="{{ $item->qty_loaded }}"
                                               name="items[{{ $item->id }}][qty_returned_expired]"
                                               value="{{ old('items.'.$item->id.'.qty_returned_expired', $item->qty_returned_expired) }}">
                                    @else
                                        {{ rtrim(rtrim(number_format($item->qty_returned_expired, 3, '.', ''), '0'), '.') }}
                                    @endif
                                </td>
                                <td class="num sub-cell">
                                    {{ rtrim(rtrim(number_format($item->qty_sold, 3, '.', ''), '0'), '.') }}
                                </td>
                                <td class="num sub-cell">
                                    Rs. {{ number_format($item->lineTotal(), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @unless ($isDraft)
                        <tfoot>
                            <tr>
                                <td colspan="5" class="num" style="text-align:right"><strong>Total amount due</strong></td>
                                <td class="num"><strong>Rs. {{ number_format($settlement->totalSalesValue(), 2) }}</strong></td>
                            </tr>
                        </tfoot>
                    @endunless
                </table>
            </div>

            @if ($isDraft)
                <div class="card-body">
                    <div class="field full">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ old('notes', $settlement->notes) }}</textarea>
                    </div>
                </div>
            @elseif ($settlement->notes)
                <div class="card-body">
                    <p class="sub-cell">{{ $settlement->notes }}</p>
                </div>
            @endif
        </div>

        @if ($isDraft)
            <div class="form-actions no-print">
                <button type="submit" class="btn btn-primary">Save returns</button>
                <a href="{{ route('admin.van-settlements.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        @endif
    </form>

    @if ($isDraft)
        <form method="POST" action="{{ route('admin.van-settlements.post-returns', $settlement) }}"
              onsubmit="return confirm('Post these returns? This restocks fresh returns and locks the counts in — they cannot be edited afterward.')">
            @csrf
            <div class="form-actions no-print">
                <button type="submit" class="btn btn-danger">Post returns</button>
            </div>
        </form>
    @endif

    {{-- Step 2: Collections / payment — only once returns are posted --}}
    @unless ($isDraft)
        <form method="POST" action="{{ route('admin.van-settlements.finalize', $settlement) }}" class="no-print">
            @csrf

            <div class="card">
                <div class="card-header">
                    <h2>Collections</h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="field {{ $errors->has('cash_collected') ? 'has-error' : '' }}">
                            <label for="cash_collected">Cash collected</label>
                            <input id="cash_collected" type="number" step="0.01" min="0" name="cash_collected"
                                   value="{{ old('cash_collected', $settlement->cash_collected) }}"
                                   {{ $isPendingPayment ? '' : 'readonly' }}>
                            @error('cash_collected') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field {{ $errors->has('online_collected') ? 'has-error' : '' }}">
                            <label for="online_collected">Online / bank collected</label>
                            <input id="online_collected" type="number" step="0.01" min="0" name="online_collected"
                                   value="{{ old('online_collected', $settlement->online_collected) }}"
                                   {{ $isPendingPayment ? '' : 'readonly' }}>
                            @error('online_collected') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field {{ $errors->has('debit_collected') ? 'has-error' : '' }}">
                            <label for="debit_collected">Debit collected (old dues paid)</label>
                            <input id="debit_collected" type="number" step="0.01" min="0" name="debit_collected"
                                   value="{{ old('debit_collected', $settlement->debit_collected) }}"
                                   {{ $isPendingPayment ? '' : 'readonly' }}>
                            @error('debit_collected') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field {{ $errors->has('new_debit_given') ? 'has-error' : '' }}">
                            <label for="new_debit_given">New debit given (credit sales today)</label>
                            <input id="new_debit_given" type="number" step="0.01" min="0" name="new_debit_given"
                                   value="{{ old('new_debit_given', $settlement->new_debit_given) }}"
                                   {{ $isPendingPayment ? '' : 'readonly' }}>
                            @error('new_debit_given') <span class="error">{{ $message }}</span> @enderror
                        </div>

                        <div class="field">
                            <label>Resulting debit balance</label>
                            <input type="text" value="Rs. {{ number_format($settlement->newDebitBalance(), 2) }}" readonly>
                        </div>

                        <div class="field">
                            <label>Reconciliation (sales − collections)</label>
                            <input type="text" value="Rs. {{ number_format($settlement->reconciliationDifference(), 2) }}" readonly>
                            <span class="help">Should read Rs. 0.00 once cash/online/debit account for every sale.</span>
                        </div>
                    </div>
                </div>
            </div>

            @if ($isPendingPayment)
                <div class="form-actions no-print">
                    <button type="submit" class="btn btn-primary">Finalize settlement</button>
                </div>
            @endif
        </form>
    @endif

@endsection
