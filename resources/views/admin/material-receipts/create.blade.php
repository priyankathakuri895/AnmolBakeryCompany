@extends('layouts.admin')

@section('title', 'Record delivery')
@section('heading', 'Record a delivery')
@section('subtitle', 'Check what arrived against the supplier\'s bill')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.material-receipts.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-grid">
                    <div class="field {{ $errors->has('supplier_id') ? 'has-error' : '' }}">
                        <label for="supplier_id">Supplier <span class="req">*</span></label>
                        <select id="supplier_id" name="supplier_id" required>
                            <option value="">Select supplier…</option>
                            @foreach ($supplierOptions as $id => $name)
                                <option value="{{ $id }}" @selected((string) old('supplier_id', request('supplier')) === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <span class="error">{{ $message }}</span> @enderror
                    </div>

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
                        <h2>Materials on this bill</h2>
                    </div>
                    <div class="table-wrap">
                        <table class="data">
                            <thead>
                                <tr>
                                    <th class="num">S.N.</th>
                                    <th>Product</th>
                                    <th>Unit</th>
                                    <th class="num">Bill qty</th>
                                    <th class="num">Received qty</th>
                                    <th class="num">Damaged qty</th>
                                    <th class="num">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($materials as $material)
                                    <tr>
                                        <td class="num sub-cell">{{ $loop->iteration }}</td>
                                        <td class="primary-cell">{{ $material->name }}</td>
                                        <td class="sub-cell">{{ $material->unit_label }}</td>
                                        <td class="num">
                                            <input type="number" step="0.001" min="0"
                                                   name="lines[{{ $material->id }}][bill_qty]"
                                                   value="{{ old('lines.'.$material->id.'.bill_qty') }}">
                                        </td>
                                        <td class="num">
                                            <input type="number" step="0.001" min="0"
                                                   name="lines[{{ $material->id }}][received_qty]"
                                                   value="{{ old('lines.'.$material->id.'.received_qty') }}">
                                        </td>
                                        <td class="num">
                                            <input type="number" step="0.001" min="0"
                                                   name="lines[{{ $material->id }}][damaged_qty]"
                                                   value="{{ old('lines.'.$material->id.'.damaged_qty') }}">
                                        </td>
                                        <td class="num">
                                            <input type="number" step="0.01" min="0"
                                                   name="lines[{{ $material->id }}][rate]"
                                                   value="{{ old('lines.'.$material->id.'.rate') }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save delivery</button>
                    <a href="{{ route('admin.material-receipts.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
