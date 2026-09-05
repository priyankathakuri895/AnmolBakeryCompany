@extends('layouts.admin')

@section('title', 'Record delivery')
@section('heading', 'Record a delivery')
@section('subtitle', 'Check what arrived against the supplier\'s bill')

@section('content')

<form method="POST" action="{{ route('admin.receipts.store') }}" enctype="multipart/form-data" id="receipt-form">
    @csrf
    <input type="hidden" name="bill_extraction" id="bill_extraction">

    {{-- ---------- Bill photo + reader ---------- --}}
    <div class="card">
        <div class="card-header">
            <h2>Supplier's bill</h2>
            <span class="hint">
                @if ($billReaderEnabled)
                    Photograph the bill and let it fill the form — then check every number.
                @else
                    Bill reading is off. The photo is still saved with this delivery.
                @endif
            </span>
        </div>

        <div class="card-body">
            <div class="bill-row">
                <div class="bill-drop">
                    <input type="file" name="bill_image" id="bill_image" accept="image/*" capture="environment" hidden>
                    <label for="bill_image" class="btn btn-outline">Choose bill photo</label>
                    <span id="bill-file-name" class="sub-cell">No file chosen</span>

                    @if ($billReaderEnabled)
                        <button type="button" id="read-bill" class="btn btn-primary" disabled>Read bill</button>
                    @endif
                </div>

                <img id="bill-preview" alt="Bill preview" hidden>
            </div>

            <div id="bill-status" class="reader-status" hidden></div>
            <ul id="bill-warnings" class="reader-warnings" hidden></ul>

            <p class="reader-note">
                Anything read from the photo is a <strong>suggestion</strong>. Nothing is saved until you check it and press Save.
            </p>
        </div>
    </div>

    {{-- ---------- Delivery header ---------- --}}
    <div class="card">
        <div class="card-header">
            <h2>Delivery details</h2>
            <span class="hint">Receipt no. {{ $nextReceiptNo }}</span>
        </div>

        <div class="card-body">
            <div class="form-grid">

                <div class="field {{ $errors->has('supplier_id') ? 'has-error' : '' }}">
                    <label for="supplier_id">Supplier <span class="req">*</span></label>
                    <select id="supplier_id" name="supplier_id" required>
                        <option value="">Select supplier…</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="field {{ $errors->has('supplier_vehicle_id') ? 'has-error' : '' }}">
                    <label for="supplier_vehicle_id">Vehicle</label>
                    <select id="supplier_vehicle_id" name="supplier_vehicle_id">
                        <option value="">Select supplier first…</option>
                    </select>
                    <span class="help">The vehicle belongs to the supplier.</span>
                    @error('supplier_vehicle_id') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label for="driver_name">Driver name</label>
                    <input id="driver_name" type="text" name="driver_name" value="{{ old('driver_name') }}">
                </div>

                <div class="field {{ $errors->has('received_date') ? 'has-error' : '' }}">
                    <label for="received_date">Received on <span class="req">*</span></label>
                    <input id="received_date" type="date" name="received_date"
                           value="{{ old('received_date', now()->toDateString()) }}" required>
                    @error('received_date') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label for="bill_number">Bill / invoice number</label>
                    <input id="bill_number" type="text" name="bill_number" value="{{ old('bill_number') }}">
                </div>

                <div class="field {{ $errors->has('bill_date') ? 'has-error' : '' }}">
                    <label for="bill_date">Bill date</label>
                    <input id="bill_date" type="date" name="bill_date" value="{{ old('bill_date') }}">
                    <span class="help" id="bill-date-raw"></span>
                    @error('bill_date') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="field full">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
                </div>

                <div class="field full">
                    <div class="checkbox-row">
                        <input id="bill_stacked" type="checkbox" name="bill_stacked" value="1" @checked(old('bill_stacked'))>
                        <label for="bill_stacked">Bill checked and filed (stacked)</label>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ---------- Line items ---------- --}}
    <div class="card">
        <div class="card-header">
            <h2>What the bill says, and what arrived</h2>
            <button type="button" id="add-line" class="btn btn-outline btn-sm">+ Add line</button>
        </div>

        <div class="table-wrap">
            <table class="data line-table">
                <thead>
                    <tr>
                        <th class="num">S.N.</th>
                        <th style="min-width:180px">Raw material</th>
                        <th class="num">Bill qty</th>
                        <th class="num">Received</th>
                        <th class="num">Damaged</th>
                        <th class="num">Accepted</th>
                        <th class="num">Pending</th>
                        <th class="num">Rate</th>
                        <th class="num">Amount</th>
                        <th style="min-width:140px">Remarks</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="line-body"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="num" style="text-align:right"><strong>Total</strong></td>
                        <td class="num" id="line-total"><strong>0.00</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="card-body">
            <p class="reader-note">
                <strong>Accepted</strong> = received − damaged, and it is the only quantity that enters stock.
                <strong>Pending</strong> = bill − received, which the supplier still owes you.
            </p>
            @error('items') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save delivery</button>
        <a href="{{ route('admin.receipts.index') }}" class="btn btn-outline">Cancel</a>
    </div>
</form>

<template id="line-template">
    <tr class="line-row">
        <td class="num sub-cell line-sn"></td>
        <td>
            <select name="items[__i__][raw_material_id]" class="line-material" required>
                <option value="">Select material…</option>
                @foreach ($materials as $material)
                    <option value="{{ $material->id }}" data-unit="{{ $material->unit_label }}">{{ $material->name }}</option>
                @endforeach
            </select>
            <span class="sub-cell line-unit"></span>
        </td>
        <td class="num"><input type="number" step="0.001" min="0" name="items[__i__][bill_qty]" class="line-bill qty" value="0" required></td>
        <td class="num"><input type="number" step="0.001" min="0" name="items[__i__][received_qty]" class="line-received qty" value="0" required></td>
        <td class="num"><input type="number" step="0.001" min="0" name="items[__i__][damaged_qty]" class="line-damaged qty" value="0"></td>
        <td class="num line-accepted">0</td>
        <td class="num line-pending">0</td>
        <td class="num"><input type="number" step="0.01" min="0" name="items[__i__][rate]" class="line-rate" value="0"></td>
        <td class="num line-amount">0.00</td>
        <td><input type="text" name="items[__i__][remarks]" class="line-remarks"></td>
        <td class="actions"><button type="button" class="btn btn-danger btn-sm remove-line">✕</button></td>
    </tr>
</template>

@php
    $vehiclesBySupplier = $suppliers->mapWithKeys(fn ($s) => [
        $s->id => $s->vehicles->where('is_active', true)->map(fn ($v) => [
            'id' => $v->id,
            'label' => $v->vehicle_number.($v->driver_name ? ' — '.$v->driver_name : ''),
            'driver' => $v->driver_name,
        ])->values(),
    ]);
@endphp

<script>
(function () {
    const VEHICLES  = @json($vehiclesBySupplier);
    const OLD_ITEMS = @json(old('items', []));
    const READ_URL  = @json(route('admin.receipts.read-bill'));
    const CSRF      = @json(csrf_token());

    const body      = document.getElementById('line-body');
    const template  = document.getElementById('line-template');
    let   index     = 0;

    /* ---------- line rows ---------- */

    function addLine(values) {
        const html = template.innerHTML.replaceAll('__i__', index++);
        // A <tbody> wrapper guarantees correct table-context parsing of the <tr> —
        // createContextualFragment is unreliable for bare table rows.
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html;
        const row = wrapper.querySelector('tr');

        if (values) {
            if (values.raw_material_id) row.querySelector('.line-material').value = values.raw_material_id;
            if (values.bill_qty      != null) row.querySelector('.line-bill').value     = values.bill_qty;
            if (values.received_qty  != null) row.querySelector('.line-received').value = values.received_qty;
            if (values.damaged_qty   != null) row.querySelector('.line-damaged').value  = values.damaged_qty;
            if (values.rate          != null) row.querySelector('.line-rate').value     = values.rate;
            if (values.remarks)               row.querySelector('.line-remarks').value  = values.remarks;
        }

        body.appendChild(row);
        recalc(row);
        renumber();
        return row;
    }

    function recalc(row) {
        const bill     = parseFloat(row.querySelector('.line-bill').value)     || 0;
        const received = parseFloat(row.querySelector('.line-received').value) || 0;
        const damaged  = parseFloat(row.querySelector('.line-damaged').value)  || 0;
        const rate     = parseFloat(row.querySelector('.line-rate').value)     || 0;

        const accepted = Math.max(received - damaged, 0);
        const pending  = Math.max(bill - received, 0);
        const amount   = bill * rate;

        const acceptedCell = row.querySelector('.line-accepted');
        const pendingCell  = row.querySelector('.line-pending');

        acceptedCell.textContent = trim(accepted);
        pendingCell.textContent  = trim(pending);
        row.querySelector('.line-amount').textContent = amount.toFixed(2);

        pendingCell.className  = 'num line-pending' + (pending  > 0 ? ' flag-amber' : '');
        acceptedCell.className = 'num line-accepted' + (damaged > 0 ? ' flag-red'   : '');

        const option = row.querySelector('.line-material').selectedOptions[0];
        row.querySelector('.line-unit').textContent = option && option.dataset.unit ? option.dataset.unit : '';

        updateTotal();
    }

    function trim(n) {
        return parseFloat(n.toFixed(3)).toString();
    }

    function renumber() {
        Array.from(body.querySelectorAll('.line-row')).forEach((row, i) => {
            row.querySelector('.line-sn').textContent = i + 1;
        });
    }

    function updateTotal() {
        const total = Array.from(body.querySelectorAll('.line-row')).reduce((sum, row) => {
            const bill = parseFloat(row.querySelector('.line-bill').value) || 0;
            const rate = parseFloat(row.querySelector('.line-rate').value) || 0;
            return sum + (bill * rate);
        }, 0);
        document.getElementById('line-total').innerHTML = '<strong>' + total.toFixed(2) + '</strong>';
    }

    body.addEventListener('input', e => {
        const row = e.target.closest('.line-row');
        if (row) recalc(row);
    });

    body.addEventListener('change', e => {
        const row = e.target.closest('.line-row');
        if (row) recalc(row);
    });

    body.addEventListener('click', e => {
        if (!e.target.classList.contains('remove-line')) return;
        e.target.closest('.line-row').remove();
        if (!body.children.length) addLine();
        renumber();
        updateTotal();
    });

    document.getElementById('add-line').addEventListener('click', () => addLine());

    /* ---------- vehicles follow the supplier ---------- */

    const supplierSelect = document.getElementById('supplier_id');
    const vehicleSelect  = document.getElementById('supplier_vehicle_id');

    function fillVehicles(selectedId) {
        const list = VEHICLES[supplierSelect.value] || [];
        vehicleSelect.innerHTML = '<option value="">' +
            (supplierSelect.value ? 'No vehicle recorded' : 'Select supplier first…') + '</option>';

        list.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = v.label;
            opt.dataset.driver = v.driver || '';
            if (String(selectedId) === String(v.id)) opt.selected = true;
            vehicleSelect.appendChild(opt);
        });
    }

    supplierSelect.addEventListener('change', () => fillVehicles(null));

    vehicleSelect.addEventListener('change', () => {
        const driver = vehicleSelect.selectedOptions[0]?.dataset.driver;
        const field  = document.getElementById('driver_name');
        if (driver && !field.value) field.value = driver;
    });

    /* ---------- bill photo + reader ---------- */

    const fileInput = document.getElementById('bill_image');
    const readBtn   = document.getElementById('read-bill');
    const status    = document.getElementById('bill-status');
    const warnings  = document.getElementById('bill-warnings');
    const preview   = document.getElementById('bill-preview');

    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        document.getElementById('bill-file-name').textContent = file ? file.name : 'No file chosen';
        if (readBtn) readBtn.disabled = !file;

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
        } else {
            preview.hidden = true;
        }
    });

    function showStatus(text, kind) {
        status.textContent = text;
        status.className = 'reader-status ' + kind;
        status.hidden = false;
    }

    if (readBtn) {
        readBtn.addEventListener('click', async () => {
            const file = fileInput.files[0];
            if (!file) return;

            readBtn.disabled = true;
            warnings.hidden = true;
            warnings.innerHTML = '';
            showStatus('Reading the bill…', 'working');

            const data = new FormData();
            data.append('bill_image', file);

            try {
                const res  = await fetch(READ_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: data,
                });
                const json = await res.json();

                if (!res.ok) {
                    showStatus(json.message || 'The bill could not be read. Enter it by hand below.', 'failed');
                    return;
                }

                applyBill(json);
            } catch (err) {
                showStatus('Could not reach the bill reader. Enter the delivery by hand below.', 'failed');
            } finally {
                readBtn.disabled = false;
            }
        });
    }

    function applyBill(bill) {
        document.getElementById('bill_extraction').value = JSON.stringify(bill);

        if (bill.supplier_id) {
            supplierSelect.value = bill.supplier_id;
            fillVehicles(bill.vehicle_id);
        }
        if (bill.bill_number && !document.getElementById('bill_number').value) {
            document.getElementById('bill_number').value = bill.bill_number;
        }
        if (bill.bill_date) {
            document.getElementById('bill_date').value = bill.bill_date;
        }
        document.getElementById('bill-date-raw').textContent =
            bill.bill_date_raw ? 'Bill shows: ' + bill.bill_date_raw : '';

        const usable = (bill.items || []).filter(i => i.material_id);

        if (usable.length) {
            body.innerHTML = '';
            usable.forEach(item => addLine({
                raw_material_id: item.material_id,
                bill_qty: item.quantity ?? 0,
                received_qty: item.quantity ?? 0,
                damaged_qty: 0,
                remarks: '',
            }));
        }

        showStatus(
            usable.length
                ? `Filled in ${usable.length} line(s) from the bill. Now count the delivery and correct anything wrong.`
                : 'Read the bill, but no material lines could be matched. Add them by hand.',
            usable.length ? 'done' : 'failed'
        );

        if ((bill.warnings || []).length) {
            warnings.innerHTML = bill.warnings.map(w => '<li>' + escapeHtml(w) + '</li>').join('');
            warnings.hidden = false;
        }
    }

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    /* ---------- first paint ---------- */

    if (OLD_ITEMS && Object.keys(OLD_ITEMS).length) {
        Object.values(OLD_ITEMS).forEach(item => addLine(item));
    } else {
        addLine();
    }

    if (supplierSelect.value) {
        fillVehicles(@json(old('supplier_vehicle_id')));
    }
})();
</script>

@endsection
