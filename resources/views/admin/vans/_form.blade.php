<div class="form-grid">

    <div class="field {{ $errors->has('name') ? 'has-error' : '' }}">
        <label for="name">Van name/number <span class="req">*</span></label>
        <input id="name" type="text" name="name" value="{{ old('name', $van->name) }}" placeholder="Van 1" required>
        @error('name') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('registration_number') ? 'has-error' : '' }}">
        <label for="registration_number">Registration number</label>
        <input id="registration_number" type="text" name="registration_number"
               value="{{ old('registration_number', $van->registration_number) }}" placeholder="BA 1 KHA 1234">
        @error('registration_number') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('default_salesman_id') ? 'has-error' : '' }}">
        <label for="default_salesman_id">Default salesman</label>
        <select id="default_salesman_id" name="default_salesman_id">
            <option value="">Unassigned</option>
            @foreach ($salesmenOptions as $id => $name)
                <option value="{{ $id }}"
                    @selected((string) old('default_salesman_id', $van->default_salesman_id) === (string) $id)>
                    {{ $name }}
                </option>
            @endforeach
        </select>
        <span class="help">Who usually drives this van. Can still be substituted on a given day.</span>
        @error('default_salesman_id') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes">{{ old('notes', $van->notes) }}</textarea>
    </div>

    <div class="field full">
        <div class="checkbox-row">
            <input id="is_active" type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $van->is_active ?? true))>
            <label for="is_active">Active — available for loading</label>
        </div>
    </div>

</div>
