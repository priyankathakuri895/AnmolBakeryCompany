@extends('layouts.admin')

@section('title', 'Follow-up delivery')
@section('heading', 'Record follow-up delivery')
@section('subtitle', 'Settling pending quantity from '.$parentReceipt->receipt_no)

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.material-receipts.follow-up.store', $parentReceipt) }}" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">
                    <div class="field {{ $errors->has('supplier_vehicle_id') ? 'has-error' : '' }}">
                        <label for="supplier_vehicle_id">Vehicle</label>
                        <select id="supplier_vehicle_id" name="supplier_vehicle_id">
                            <option value="">Unknown / not listed</option>
                            @foreach ($vehicleOptions as $id => $label)
                                <option value="{{ $id }}" @selected((string) old('supplier_vehicle_id') === (string) $id)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('supplier_vehicle_id') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('driver_name') ? 'has-error' : '' }}">
                        <label for="driver_name">Driver name</label>
                        <input id="driver_name" type="text" name="driver_name" value="{{ old('driver_name') }}">
                        @error('driver_name') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('received_date') ? 'has-error' : '' }}">
                        <label for="received_date">Date received <span class="req">*</span></label>
                        <input id="received_date" type="date" name="received_date"
                               value="{{ old('received_date', now()->toDateString()) }}" required>
                        @error('received_date') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('bill_number') ? 'has-error' : '' }}">
                        <label for="bill_number">Bill / invoice number</label>
                        <input id="bill_number" type="text" name="bill_number" value="{{ old('bill_number') }}">
                        <span class="help">Only if this delivery came with its own new bill.</span>
                        @error('bill_number') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('bill_date') ? 'has-error' : '' }}">
                        <label for="bill_date">Bill date</label>
                        <input id="bill_date" type="date" name="bill_date" value="{{ old('bill_date') }}">
                        @error('bill_date') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('bill_image') ? 'has-error' : '' }}">
                        <label for="bill_image">Bill photo</label>
                        <input id="bill_image" type="file" name="bill_image" accept="image/*">
                        <span class="help">JPG/PNG/WebP, up to 5 MB.</span>
                        @error('bill_image') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field full">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="card" style="margin-top:20px">
                    <div class="card-header">
                        <h2>Pending materials</h2>
                    </div>
                    <div class="table-wrap">
                        <table class="data">
                            <thead>
                                <tr>
                                    <th class="num">S.N.</th>
                                    <th>Product</th>
                                    <th>Unit</th>
                                    <th class="num">Still owed</th>
                                    <th class="num">Received now</th>
                                    <th class="num">Damaged</th>
                                    <th class="num">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingItems as $item)
                                    <tr>
                                        <td class="num sub-cell">{{ $loop->iteration }}</td>
                                        <td class="primary-cell">{{ $item->rawMaterial->name }}</td>
                                        <td class="sub-cell">{{ $item->unit_label }}</td>
                                        <td class="num sub-cell">
                                            {{ rtrim(rtrim(number_format($item->pending_qty, 3, '.', ''), '0'), '.') }}
                                        </td>
                                        <td class="num">
                                            <input type="number" step="0.001" min="0" max="{{ $item->pending_qty }}"
                                                   name="lines[{{ $item->id }}][received_qty]"
                                                   value="{{ old('lines.'.$item->id.'.received_qty') }}">
                                        </td>
                                        <td class="num">
                                            <input type="number" step="0.001" min="0" max="{{ $item->pending_qty }}"
                                                   name="lines[{{ $item->id }}][damaged_qty]"
                                                   value="{{ old('lines.'.$item->id.'.damaged_qty') }}">
                                        </td>
                                        <td class="num">
                                            <input type="number" step="0.01" min="0"
                                                   name="lines[{{ $item->id }}][rate]"
                                                   value="{{ old('lines.'.$item->id.'.rate', $item->rate) }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save follow-up delivery</button>
                    <a href="{{ route('admin.material-receipts.show', $parentReceipt) }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
