@extends('layouts.admin')

@section('title', 'Stock Check '.$stockCheck->check_no)
@section('heading', $stockCheck->check_no)
@section('subtitle', 'Physical stock check for finished products')

@section('topbar')
    <a href="{{ route('admin.product-stock-checks.index') }}" class="btn btn-outline btn-sm">Back to stock checks</a>
@endsection

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="form-grid">
                <div class="field">
                    <label>Status</label>
                    <input type="text" value="{{ $stockCheck->status->label() }}" readonly>
                </div>
                <div class="field">
                    <label>Discrepancies</label>
                    <input type="text"
                           value="{{ $stockCheck->items->where('difference', '!=', 0)->count() }} of {{ $stockCheck->items->count() }}"
                           readonly>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.product-stock-checks.update', $stockCheck) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-body">
                <div class="form-grid">
                    <div class="field {{ $errors->has('check_date') ? 'has-error' : '' }}">
                        <label for="check_date">Check date <span class="req">*</span></label>
                        <input id="check_date" type="date" name="check_date"
                               value="{{ old('check_date', $stockCheck->check_date->toDateString()) }}"
                               {{ $stockCheck->isDraft() ? '' : 'readonly' }} required>
                        @error('check_date') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field full">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" {{ $stockCheck->isDraft() ? '' : 'readonly' }}>{{ old('notes', $stockCheck->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Product counts</h2>
            </div>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="num">System qty</th>
                            <th class="num">Physical qty</th>
                            <th class="num">Difference</th>
                            <th>Reason</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stockCheck->items as $item)
                            <tr>
                                <td class="primary-cell">
                                    {{ $item->product->name }}
                                    <br><span class="sub-cell">{{ $item->product->unit_label }}</span>
                                </td>
                                <td class="num sub-cell">
                                    {{ rtrim(rtrim(number_format($item->system_qty, 3, '.', ''), '0'), '.') }}
                                </td>
                                <td class="num">
                                    @if ($stockCheck->isDraft())
                                        <input type="number" step="0.001" min="0"
                                               name="items[{{ $item->id }}][physical_qty]"
                                               value="{{ old('items.'.$item->id.'.physical_qty', $item->physical_qty) }}">
                                    @else
                                        {{ rtrim(rtrim(number_format($item->physical_qty, 3, '.', ''), '0'), '.') }}
                                    @endif
                                </td>
                                <td class="num sub-cell">
                                    {{ $item->difference > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($item->difference, 3, '.', ''), '0'), '.') }}
                                </td>
                                <td>
                                    @if ($stockCheck->isDraft())
                                        <select name="items[{{ $item->id }}][reason]">
                                            <option value="">—</option>
                                            @foreach ($reasons as $reason)
                                                <option value="{{ $reason->value }}"
                                                    @selected(old('items.'.$item->id.'.reason', $item->reason?->value) === $reason->value)>
                                                    {{ $reason->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $item->reason?->label() ?: '—' }}
                                    @endif
                                </td>
                                <td>
                                    @if ($stockCheck->isDraft())
                                        <input type="text" name="items[{{ $item->id }}][reason_note]"
                                               value="{{ old('items.'.$item->id.'.reason_note', $item->reason_note) }}">
                                    @else
                                        {{ $item->reason_note ?: '—' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($stockCheck->isDraft())
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save counts</button>
                <a href="{{ route('admin.product-stock-checks.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        @endif
    </form>

    @if ($stockCheck->isDraft())
        <form method="POST" action="{{ route('admin.product-stock-checks.finalize', $stockCheck) }}"
              onsubmit="return confirm('Finalize this stock check? This posts adjustments to stock and cannot be undone.')">
            @csrf
            <div class="form-actions">
                <button type="submit" class="btn btn-danger">Finalize stock check</button>
            </div>
        </form>
    @endif

@endsection
