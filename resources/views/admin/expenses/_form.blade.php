<div class="form-grid">

    <div class="field {{ $errors->has('expense_date') ? 'has-error' : '' }}">
        <label for="expense_date">Date <span class="req">*</span></label>
        <input id="expense_date" type="date" name="expense_date"
               value="{{ old('expense_date', optional($expense->expense_date)->toDateString() ?? now()->toDateString()) }}" required>
        @error('expense_date') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('van_id') ? 'has-error' : '' }}">
        <label for="van_id">Van</label>
        <select id="van_id" name="van_id">
            <option value="">General (not van-specific)</option>
            @foreach ($vanOptions as $id => $name)
                <option value="{{ $id }}" @selected((string) old('van_id', $expense->van_id) === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('van_id') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('category') ? 'has-error' : '' }}">
        <label for="category">Category <span class="req">*</span></label>
        <select id="category" name="category" required>
            <option value="">Select category…</option>
            @foreach ($categories as $category)
                <option value="{{ $category->value }}"
                    @selected(old('category', $expense->category?->value) === $category->value)>
                    {{ $category->label() }}
                </option>
            @endforeach
        </select>
        @error('category') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field {{ $errors->has('amount') ? 'has-error' : '' }}">
        <label for="amount">Amount <span class="req">*</span></label>
        <input id="amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount) }}" required>
        @error('amount') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="field full">
        <label for="description">Description</label>
        <textarea id="description" name="description">{{ old('description', $expense->description) }}</textarea>
    </div>

</div>
