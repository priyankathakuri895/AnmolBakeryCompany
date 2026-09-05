<div class="form-grid">

    <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
        <label for="name">Name <span class="req">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $salesman->name) }}" required>
        @error('name') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('phone') ? 'has-error' : '' }}">
        <label for="phone">Phone</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone', $salesman->phone) }}">
        @error('phone') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field full {{ $errors->has('address') ? 'has-error' : '' }}">
        <label for="address">Address</label>
        <textarea id="address" name="address">{{ old('address', $salesman->address) }}</textarea>
        @error('address') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes">{{ old('notes', $salesman->notes) }}</textarea>
    </div>

    <div class="field {{ $errors->has('id_document') ? 'has-error' : '' }}">
        <label for="id_document">ID document (citizenship / license)</label>
        <input id="id_document" type="file" name="id_document" accept="image/*">
        <span class="help">JPG/PNG/WebP, up to 5 MB.</span>
        @error('id_document') <span class="error">{{ $message }}</span> @enderror
    </div>

    @if ($salesman->idDocumentUrl())
        <div class="field">
            <label>Current document</label>
            <a href="{{ $salesman->idDocumentUrl() }}" target="_blank" rel="noopener">
                <img src="{{ $salesman->idDocumentUrl() }}" alt="ID document" style="max-width:160px;max-height:100px;border:1px solid var(--admin-border);border-radius:var(--radius)">
            </a>
            <span class="help">Uploading a new file replaces this one.</span>
        </div>
    @endif

    <div class="field full">
        <div class="checkbox-row">
            <input id="is_active" type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $salesman->is_active ?? true))>
            <label for="is_active">Active — available to assign to a van</label>
        </div>
    </div>

</div>
