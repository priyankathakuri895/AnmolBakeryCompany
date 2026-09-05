@extends('layouts.admin')

@section('title', 'Start stock check')
@section('heading', 'Start a stock check')
@section('subtitle', "Snapshots every active product's system stock so you can enter physical counts")

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.product-stock-checks.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="field {{ $errors->has('check_date') ? 'has-error' : '' }}">
                        <label for="check_date">Check date <span class="req">*</span></label>
                        <input id="check_date" type="date" name="check_date"
                               value="{{ old('check_date', now()->toDateString()) }}" required>
                        @error('check_date') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field full">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Start stock check</button>
                    <a href="{{ route('admin.product-stock-checks.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
