<div class="form-grid">

    <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
        <label for="name">Supplier / wholesaler name <span class="req">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $supplier->name) }}" required>
        @error('name') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('code') ? 'has-error' : '' }}">
        <label for="code">Short code</label>
        <input id="code" type="text" name="code" value="{{ old('code', $supplier->code) }}">
        <span class="help">Optional. Must be unique, e.g. SUP-01</span>
        @error('code') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field">
        <label for="contact_person">Contact person</label>
        <input id="contact_person" type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}">
    </div>

    <div class="field">
        <label for="phone">Phone</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $supplier->phone) }}">
    </div>

    <div class="field">
        <label for="alt_phone">Alternate phone</label>
        <input id="alt_phone" type="text" name="alt_phone" value="{{ old('alt_phone', $supplier->alt_phone) }}">
    </div>

    <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $supplier->email) }}">
        @error('email') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field full">
        <label for="address">Address</label>
        <textarea id="address" name="address">{{ old('address', $supplier->address) }}</textarea>
    </div>

    <div class="field full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes">{{ old('notes', $supplier->notes) }}</textarea>
        <span class="help">What they usually supply, payment terms, anything worth remembering.</span>
    </div>

    <div class="field full">
        <div class="checkbox-row">
            <input id="is_active" type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $supplier->is_active ?? true))>
            <label for="is_active">Active — show this supplier when recording a delivery</label>
        </div>
    </div>

</div>
