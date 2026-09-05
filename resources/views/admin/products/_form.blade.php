@php($isNew = ! $product->exists)

<div class="form-grid">

    <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
        <label for="name">Product name <span class="req">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required>
        @error('name') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('code') ? 'has-error' : '' }}">
        <label for="code">Short code</label>
        <input id="code" type="text" name="code" value="{{ old('code', $product->code) }}">
        <span class="help">Optional, must be unique.</span>
        @error('code') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('unit_label') ? 'has-error' : '' }}">
        <label for="unit_label">Sold as <span class="req">*</span></label>
        <input id="unit_label" type="text" name="unit_label"
               value="{{ old('unit_label', $product->unit_label ?: 'Piece') }}"
               list="unit-labels" required>
        <datalist id="unit-labels">
            <option value="Piece"></option>
            <option value="Packet"></option>
            <option value="Dozen"></option>
            <option value="Tray"></option>
            <option value="Box"></option>
        </datalist>
        @error('unit_label') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('price') ? 'has-error' : '' }}">
        <label for="price">Selling price <span class="req">*</span></label>
        <input id="price" type="number" step="0.01" min="0" name="price"
               value="{{ old('price', $product->price ?: 0) }}" required>
        <span class="help">Price per {{ old('unit_label', $product->unit_label ?: 'unit') }}.</span>
        @error('price') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('opening_stock') ? 'has-error' : '' }}">
        <label for="opening_stock">Opening stock</label>
        <input id="opening_stock" type="number" step="0.001" min="0" name="opening_stock"
               value="{{ old('opening_stock', $product->opening_stock ?: 0) }}"
               {{ $isNew ? '' : 'readonly' }}>
        <span class="help">
            @if ($isNew)
                Stock already on hand today. This writes an opening entry in the stock ledger.
            @else
                Set once at creation. Stock changes now come from production, loading and returns.
            @endif
        </span>
        @error('opening_stock') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('reorder_level') ? 'has-error' : '' }}">
        <label for="reorder_level">Reorder level</label>
        <input id="reorder_level" type="number" step="0.001" min="0" name="reorder_level"
               value="{{ old('reorder_level', $product->reorder_level) }}">
        <span class="help">Warn me when stock drops to this. Leave blank for no warning.</span>
        @error('reorder_level') <span class="error">{{ $message }}</span> @enderror
    </div>

    @unless ($isNew)
        <div class="field">
            <label>Current stock</label>
            <input type="text" value="{{ rtrim(rtrim(number_format($product->current_stock, 3, '.', ''), '0'), '.') }} {{ $product->unit_label }}" readonly>
        </div>
    @endunless

    <div class="field full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes">{{ old('notes', $product->notes) }}</textarea>
    </div>

    <div class="field full">
        <div class="checkbox-row">
            <input id="is_active" type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $product->is_active ?? true))>
            <label for="is_active">Active — show this product when loading a van</label>
        </div>
    </div>

</div>
