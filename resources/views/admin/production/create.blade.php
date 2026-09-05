@extends('layouts.admin')

@section('title', 'Record production')
@section('heading', 'Record production')
@section('subtitle', 'Add finished-goods stock baked today')

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.production.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field {{ $errors->has('product_id') ? 'has-error' : '' }}">
                        <label for="product_id">Product <span class="req">*</span></label>
                        <select id="product_id" name="product_id" required>
                            <option value="">Select product…</option>
                            @foreach ($productOptions as $id => $name)
                                <option value="{{ $id }}" @selected((string) old('product_id') === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('product_id') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('transaction_date') ? 'has-error' : '' }}">
                        <label for="transaction_date">Date <span class="req">*</span></label>
                        <input id="transaction_date" type="date" name="transaction_date"
                               value="{{ old('transaction_date', now()->toDateString()) }}" required>
                        @error('transaction_date') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('quantity') ? 'has-error' : '' }}">
                        <label for="quantity">Quantity produced <span class="req">*</span></label>
                        <input id="quantity" type="number" step="0.001" min="0.001" name="quantity" value="{{ old('quantity') }}" required>
                        @error('quantity') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field full">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save entry</button>
                    <a href="{{ route('admin.production.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
