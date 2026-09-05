@extends('layouts.admin')

@section('title', 'Follow-up delivery')
@section('heading', 'Follow-up delivery')
@section('subtitle', 'Material that was short on '.$receipt->receipt_no.' and has now arrived')

@section('content')

<form method="POST" action="{{ route('admin.receipts.follow-up.store', $receipt) }}">
    @csrf

    <div class="card">
        <div class="card-header">
            <h2>Delivery details</h2>
            <span class="hint">Supplier: {{ $receipt->supplier->name }}</span>
        </div>

        <div class="card-body">
            <div class="form-grid">

                <div class="field {{ $errors->has('received_date') ? 'has-error' : '' }}">
                    <label for="received_date">Received on <span class="req">*</span></label>
                    <input id="received_date" type="date" name="received_date"
                           value="{{ old('received_date', now()->toDateString()) }}" required>
                    @error('received_date') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="field {{ $errors->has('supplier_vehicle_id') ? 'has-error' : '' }}">
                    <label for="supplier_vehicle_id">Vehicle</label>
                    <select id="supplier_vehicle_id" name="supplier_vehicle_id">
                        <option value="">—</option>
                        @foreach ($receipt->supplier->vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('supplier_vehicle_id') == $vehicle->id)>
                                {{ $vehicle->vehicle_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_vehicle_id') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label for="driver_name">Driver name</label>
                    <input id="driver_name" type="text" name="driver_name" value="{{ old('driver_name') }}">
                </div>

                <div class="field">
                    <label for="bill_number">Bill number</label>
                    <input id="bill_number" type="text" name="bill_number"
                           value="{{ old('bill_number', $receipt->bill_number) }}">
                    <span class="help">Often the same bill as the original delivery.</span>
                </div>

                <div class="field">
                    <label for="bill_date">Bill date</label>
                    <input id="bill_date" type="date" name="bill_date" value="{{ old('bill_date') }}">
                </div>

                <div class="field full">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Still owed on {{ $receipt->receipt_no }}</h2>
            <span class="hint">Leave a line at 0 if it did not arrive this time</span>
        </div>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th class="num">S.N.</th>
                        <th>Material</th>
                        <th class="num">Still owed</th>
                        <th class="num">Arrived now</th>
                        <th class="num">Damaged</th>
                        <th class="num">Rate</th>
                        <th style="min-width:160px">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendingItems as $index => $item)
                        <tr>
                            <td class="num sub-cell">{{ $loop->iteration }}</td>
                            <td>
                                <span class="primary-cell">{{ $item->rawMaterial->name }}</span>
                                <br><span class="sub-cell">{{ $item->unit_label }}</span>
                                <input type="hidden" name="items[{{ $index }}][parent_item_id]" value="{{ $item->id }}">
                            </td>
                            <td class="num flag-amber"><strong>{{ $item->pending_qty + 0 }}</strong></td>
                            <td class="num">
                                <input type="number" step="0.001" min="0" class="qty"
                                       name="items[{{ $index }}][received_qty]"
                                       value="{{ old("items.$index.received_qty", 0) }}" required>
                            </td>
                            <td class="num">
                                <input type="number" step="0.001" min="0" class="qty"
                                       name="items[{{ $index }}][damaged_qty]"
                                       value="{{ old("items.$index.damaged_qty", 0) }}">
                            </td>
                            <td class="num">
                                <input type="number" step="0.01" min="0"
                                       name="items[{{ $index }}][rate]"
                                       value="{{ old("items.$index.rate", $item->rate) }}">
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][remarks]"
                                       value="{{ old("items.$index.remarks") }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-body">
            <p class="reader-note">
                Saving this adds the accepted quantity to stock and reduces what
                {{ $receipt->supplier->name }} still owes on {{ $receipt->receipt_no }}.
                If anything is still short, that delivery stays <strong>Pending</strong>.
            </p>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save follow-up delivery</button>
        <a href="{{ route('admin.receipts.show', $receipt) }}" class="btn btn-outline">Cancel</a>
    </div>
</form>

@endsection
