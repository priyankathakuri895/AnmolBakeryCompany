@extends('layouts.admin')

@section('title', 'Add debit adjustment')
@section('heading', 'Add a debit adjustment')
@section('subtitle', 'Manually correct a van\'s outstanding debit balance')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.van-debit.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field {{ $errors->has('van_id') ? 'has-error' : '' }}">
                        <label for="van_id">Van <span class="req">*</span></label>
                        <select id="van_id" name="van_id" required>
                            <option value="">Select van…</option>
                            @foreach ($vanOptions as $id => $name)
                                <option value="{{ $id }}" @selected((string) old('van_id') === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('van_id') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('direction') ? 'has-error' : '' }}">
                        <label for="direction">Direction <span class="req">*</span></label>
                        <select id="direction" name="direction" required>
                            <option value="decrease" @selected(old('direction', 'decrease') === 'decrease')>Decrease debit (van owes less)</option>
                            <option value="increase" @selected(old('direction') === 'increase')>Increase debit (van owes more)</option>
                        </select>
                        @error('direction') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('amount') ? 'has-error' : '' }}">
                        <label for="amount">Amount <span class="req">*</span></label>
                        <input id="amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required>
                        @error('amount') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('transaction_date') ? 'has-error' : '' }}">
                        <label for="transaction_date">Date <span class="req">*</span></label>
                        <input id="transaction_date" type="date" name="transaction_date"
                               value="{{ old('transaction_date', now()->toDateString()) }}" required>
                        @error('transaction_date') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field full">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" placeholder="Why is this being adjusted manually?">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save adjustment</button>
                    <a href="{{ route('admin.van-debit.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
