<div class="form-grid">

    <div class="field {{ $errors->has('supplier_id') ? 'has-error' : '' }}">
        <label for="supplier_id">Supplier <span class="req">*</span></label>
        <select id="supplier_id" name="supplier_id" required>
            <option value="">Select supplier…</option>
            @foreach ($supplierOptions as $id => $name)
                <option value="{{ $id }}"
                    @selected((string) old('supplier_id', $vehicle->supplier_id ?? request('supplier')) === (string) $id)>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        <span class="help">The vehicle belongs to this supplier.</span>
        @error('supplier_id') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('vehicle_number') ? 'has-error' : '' }}">
        <label for="vehicle_number">Vehicle number <span class="req">*</span></label>
        <input id="vehicle_number" type="text" name="vehicle_number"
               value="{{ old('vehicle_number', $vehicle->vehicle_number) }}"
               placeholder="BA 1 KHA 1234" required>
        @error('vehicle_number') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label for="driver_name">Driver name</label>
        <input id="driver_name" type="text" name="driver_name" value="{{ old('driver_name', $vehicle->driver_name) }}">
        <span class="help">The usual driver. You can still type a different name on a delivery.</span>
    </div>

    <div class="field">
        <label for="driver_phone">Driver phone</label>
        <input id="driver_phone" type="text" name="driver_phone" value="{{ old('driver_phone', $vehicle->driver_phone) }}">
    </div>

    <div class="field full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes">{{ old('notes', $vehicle->notes) }}</textarea>
    </div>

    <div class="field full">
        <div class="checkbox-row">
            <input id="is_active" type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $vehicle->is_active ?? true))>
            <label for="is_active">Active — show this vehicle when recording a delivery</label>
        </div>
    </div>

</div>
