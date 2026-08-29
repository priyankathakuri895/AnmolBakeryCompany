@php($isNew = ! $material->exists)

<div class="form-grid">

    <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
        <label for="name">Material name <span class="req">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $material->name) }}" required>
        @error('name') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('code') ? 'has-error' : '' }}">
        <label for="code">Short code</label>
        <input id="code" type="text" name="code" value="{{ old('code', $material->code) }}">
        <span class="help">Optional, must be unique.</span>
        @error('code') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('unit_label') ? 'has-error' : '' }}">
        <label for="unit_label">Counting unit <span class="req">*</span></label>
        <input id="unit_label" type="text" name="unit_label"
               value="{{ old('unit_label', $material->unit_label ?: 'Packet') }}"
               list="unit-labels" required>
        <datalist id="unit-labels">
            <option value="Packet"></option>
            <option value="Drum"></option>
            <option value="Sack"></option>
            <option value="Box"></option>
            <option value="Tin"></option>
        </datalist>
        <span class="help">How staff physically count it.</span>
        @error('unit_label') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('unit_size') ? 'has-error' : '' }}">
        <label for="unit_size">Size of one unit <span class="req">*</span></label>
        <input id="unit_size" type="number" step="0.001" min="0.001" name="unit_size"
               value="{{ old('unit_size', $material->unit_size ?: 1) }}" required>
        <span class="help">1 packet = 50 kg → enter 50.</span>
        @error('unit_size') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('base_unit') ? 'has-error' : '' }}">
        <label for="base_unit">Measured in <span class="req">*</span></label>
        <select id="base_unit" name="base_unit" required>
            @foreach ($baseUnits as $unit)
                <option value="{{ $unit->value }}"
                    @selected(old('base_unit', $material->base_unit?->value ?? 'kg') === $unit->value)>
                    {{ $unit->label() }}
                </option>
            @endforeach
        </select>
        @error('base_unit') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('opening_stock') ? 'has-error' : '' }}">
        <label for="opening_stock">Opening stock</label>
        <input id="opening_stock" type="number" step="0.001" min="0" name="opening_stock"
               value="{{ old('opening_stock', $material->opening_stock ?: 0) }}"
               {{ $isNew ? '' : 'readonly' }}>
        <span class="help">
            @if ($isNew)
                Stock already in the warehouse today, in {{ old('unit_label', $material->unit_label ?: 'packets') }}.
                This writes an opening entry in the stock ledger.
            @else
                Set once at creation. Stock changes now come from receiving and stock checks, so the ledger stays honest.
            @endif
        </span>
        @error('opening_stock') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('reorder_level') ? 'has-error' : '' }}">
        <label for="reorder_level">Reorder level</label>
        <input id="reorder_level" type="number" step="0.001" min="0" name="reorder_level"
               value="{{ old('reorder_level', $material->reorder_level) }}">
        <span class="help">Warn me when stock drops to this. Leave blank for no warning.</span>
        @error('reorder_level') <span class="error">{{ $message }}</span> @enderror
    </div>

    @unless ($isNew)
        <div class="field">
            <label>Current stock</label>
            <input type="text" value="{{ rtrim(rtrim(number_format($material->current_stock, 3, '.', ''), '0'), '.') }} {{ $material->unit_label }}" readonly>
            <span class="help">= {{ number_format($material->current_base_stock, 2) }} {{ $material->base_unit->label() }}</span>
        </div>
    @endunless

    <div class="field full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes">{{ old('notes', $material->notes) }}</textarea>
    </div>

    <div class="field full">
        <div class="checkbox-row">
            <input id="is_active" type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $material->is_active ?? true))>
            <label for="is_active">Active — show this material when recording a delivery</label>
        </div>
    </div>

</div>
