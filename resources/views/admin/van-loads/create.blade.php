@extends('layouts.admin')

@section('title', 'Load a van')
@section('heading', 'Load a van')
@section('subtitle', "Move today's stock from the warehouse onto a van")

@section('content')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.van-loads.store') }}">
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

                    <div class="field {{ $errors->has('salesman_id') ? 'has-error' : '' }}">
                        <label for="salesman_id">Salesman <span class="req">*</span></label>
                        <select id="salesman_id" name="salesman_id" required>
                            <option value="">Select salesman…</option>
                            @foreach ($salesmenOptions as $id => $name)
                                <option value="{{ $id }}" @selected((string) old('salesman_id') === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                        <span class="help">Who is actually driving today — can differ from the van's default.</span>
                        @error('salesman_id') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field {{ $errors->has('load_date') ? 'has-error' : '' }}">
                        <label for="load_date">Load date <span class="req">*</span></label>
                        <input id="load_date" type="date" name="load_date"
                               value="{{ old('load_date', now()->toDateString()) }}" required>
                        @error('load_date') <span class="error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field full">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="card" style="margin-top:20px">
                    <div class="card-header">
                        <h2>Quantities to load</h2>
                    </div>
                    <div class="table-wrap">
                        <table class="data">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="num">In stock</th>
                                    <th class="num">Qty to load</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="primary-cell">
                                            {{ $product->name }}
                                            <span class="sub-cell">{{ $product->unit_label }}</span>
                                        </td>
                                        <td class="num sub-cell">
                                            {{ rtrim(rtrim(number_format($product->current_stock, 3, '.', ''), '0'), '.') }}
                                        </td>
                                        <td class="num">
                                            <input type="number" step="0.001" min="0"
                                                   max="{{ $product->current_stock }}"
                                                   name="quantities[{{ $product->id }}]"
                                                   value="{{ old('quantities.'.$product->id) }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Load van</button>
                    <a href="{{ route('admin.van-loads.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

@endsection
