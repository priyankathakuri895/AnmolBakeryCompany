<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReceiptLinkType;
use App\Enums\ReceiptStatus;
use App\Enums\ReceiptType;
use App\Enums\StockTransactionType;
use App\Http\Controllers\Controller;
use App\Models\MaterialReceipt;
use App\Models\MaterialReceiptItem;
use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Services\BillReading\BillReader;
use App\Services\StockPoster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialReceiptController extends Controller
{
    public function __construct(private StockPoster $stock) {}

    public function index(Request $request): View
    {
        $receipts = MaterialReceipt::query()
            ->with(['supplier', 'vehicle'])
            ->withCount('items')
            ->when($request->filled('supplier'), fn ($q) => $q->where('supplier_id', $request->integer('supplier')))
            ->when($request->input('status') === 'pending', fn ($q) => $q->where('status', ReceiptStatus::Pending))
            ->when($request->input('status') === 'complete', fn ($q) => $q->where('status', ReceiptStatus::Complete))
            ->when($request->input('status') === 'unstacked', fn ($q) => $q->where('bill_stacked', false))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = '%'.$request->string('search')->trim().'%';

                $query->where(fn ($q) => $q->where('receipt_no', 'like', $search)
                    ->orWhere('bill_number', 'like', $search));
            })
            ->latest('received_date')
            ->latest('id')
            ->paginate(15);

        return view('admin.receipts.index', [
            'receipts' => $receipts,
            'supplierOptions' => Supplier::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create(BillReader $reader): View
    {
        return view('admin.receipts.create', [
            'suppliers' => Supplier::active()->with('vehicles')->orderBy('name')->get(),
            'materials' => RawMaterial::active()->orderBy('name')->get(),
            'billReaderEnabled' => $reader->enabled(),
            'nextReceiptNo' => MaterialReceipt::nextReceiptNo(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateReceipt($request);

        $receipt = DB::transaction(function () use ($request, $data) {
            $receipt = MaterialReceipt::create([
                'receipt_no' => MaterialReceipt::nextReceiptNo(),
                'supplier_id' => $data['supplier_id'],
                'supplier_vehicle_id' => $data['supplier_vehicle_id'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'received_date' => $data['received_date'],
                'bill_number' => $data['bill_number'] ?? null,
                'bill_date' => $data['bill_date'] ?? null,
                'bill_image_path' => $this->storeBillImage($request),
                'bill_extraction' => $this->decodeExtraction($request),
                'bill_read_at' => $request->filled('bill_extraction') ? now() : null,
                'bill_stacked' => $request->boolean('bill_stacked'),
                'bill_stacked_at' => $request->boolean('bill_stacked') ? now() : null,
                'receipt_type' => ReceiptType::Initial,
                'status' => ReceiptStatus::Pending,
                'notes' => $data['notes'] ?? null,
                'received_by' => $request->user()?->id,
            ]);

            foreach ($data['items'] as $row) {
                $this->addLine(
                    receipt: $receipt,
                    material: RawMaterial::findOrFail($row['raw_material_id']),
                    billQty: (float) $row['bill_qty'],
                    rate: (float) ($row['rate'] ?? 0),
                    receivedQty: (float) $row['received_qty'],
                    damagedQty: (float) ($row['damaged_qty'] ?? 0),
                    remarks: $row['remarks'] ?? null,
                    linkType: ReceiptLinkType::Initial,
                    userId: $request->user()?->id,
                );
            }

            return $receipt->refreshStatus();
        });

        return redirect()
            ->route('admin.receipts.show', $receipt)
            ->with('success', "Delivery {$receipt->receipt_no} recorded.");
    }

    public function show(MaterialReceipt $receipt): View
    {
        $receipt->load([
            'supplier', 'vehicle', 'receiver', 'parentReceipt',
            'items.rawMaterial', 'items.childItems.receipt',
            'followUpReceipts.items.rawMaterial',
        ]);

        return view('admin.receipts.show', compact('receipt'));
    }

    /** Mark the paper bill as checked and filed. */
    public function stackBill(MaterialReceipt $receipt): RedirectResponse
    {
        $stacked = ! $receipt->bill_stacked;

        $receipt->update([
            'bill_stacked' => $stacked,
            'bill_stacked_at' => $stacked ? now() : null,
        ]);

        return back()->with('success', $stacked ? 'Bill marked as stacked.' : 'Bill marked as not stacked.');
    }

    /** Form for the later delivery that settles what was short. */
    public function createFollowUp(MaterialReceipt $receipt): View|RedirectResponse
    {
        $receipt->load(['supplier.vehicles', 'items.rawMaterial']);

        $pendingItems = $receipt->items->filter(
            fn (MaterialReceiptItem $item) => $item->link_type === ReceiptLinkType::Initial
                && $item->outstanding() > 0
        );

        if ($pendingItems->isEmpty()) {
            return redirect()
                ->route('admin.receipts.show', $receipt)
                ->with('error', 'Nothing is pending on this delivery.');
        }

        return view('admin.receipts.follow-up', [
            'receipt' => $receipt,
            'pendingItems' => $pendingItems,
        ]);
    }

    public function storeFollowUp(Request $request, MaterialReceipt $receipt): RedirectResponse
    {
        $receipt->load('items');

        $data = $request->validate([
            'received_date' => ['required', 'date'],
            'supplier_vehicle_id' => [
                'nullable',
                Rule::exists('supplier_vehicles', 'id')->where('supplier_id', $receipt->supplier_id),
            ],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'bill_number' => ['nullable', 'string', 'max:100'],
            'bill_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.parent_item_id' => ['required', 'integer'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
            'items.*.damaged_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $lines = collect($data['items'])->filter(fn ($row) => (float) $row['received_qty'] > 0);

        if ($lines->isEmpty()) {
            return back()
                ->withInput()
                ->with('error', 'Enter how much arrived on at least one line.');
        }

        foreach ($lines as $row) {
            $parent = $receipt->items->firstWhere('id', (int) $row['parent_item_id']);

            if (! $parent) {
                return back()->withInput()->with('error', 'One of those lines does not belong to this delivery.');
            }

            if ((float) ($row['damaged_qty'] ?? 0) > (float) $row['received_qty']) {
                return back()->withInput()->with(
                    'error',
                    "Damaged cannot be more than received for {$parent->rawMaterial->name}."
                );
            }
        }

        $followUp = DB::transaction(function () use ($request, $receipt, $data, $lines) {
            $followUp = MaterialReceipt::create([
                'receipt_no' => MaterialReceipt::nextReceiptNo(),
                'supplier_id' => $receipt->supplier_id,
                'supplier_vehicle_id' => $data['supplier_vehicle_id'] ?? null,
                'driver_name' => $data['driver_name'] ?? null,
                'received_date' => $data['received_date'],
                'bill_number' => $data['bill_number'] ?? $receipt->bill_number,
                'bill_date' => $data['bill_date'] ?? null,
                'bill_stacked' => false,
                'receipt_type' => ReceiptType::FollowUp,
                'parent_receipt_id' => $receipt->id,
                'status' => ReceiptStatus::Complete,
                'notes' => $data['notes'] ?? null,
                'received_by' => $request->user()?->id,
            ]);

            foreach ($lines as $row) {
                $parent = $receipt->items->firstWhere('id', (int) $row['parent_item_id']);

                $this->addLine(
                    receipt: $followUp,
                    material: $parent->rawMaterial,
                    // What was still owed when this delivery arrived.
                    billQty: $parent->outstanding(),
                    rate: (float) ($row['rate'] ?? $parent->rate),
                    receivedQty: (float) $row['received_qty'],
                    damagedQty: (float) ($row['damaged_qty'] ?? 0),
                    remarks: $row['remarks'] ?? null,
                    linkType: ReceiptLinkType::PendingFulfilment,
                    userId: $request->user()?->id,
                    parent: $parent,
                );
            }

            $followUp->refreshStatus();

            // The original receipt now owes less — or nothing at all.
            $receipt->refreshStatus();

            return $followUp;
        });

        return redirect()
            ->route('admin.receipts.show', $receipt)
            ->with('success', "Follow-up delivery {$followUp->receipt_no} recorded against {$receipt->receipt_no}.");
    }

    /** Read an uploaded bill photo and return suggestions as JSON. Saves nothing. */
    public function readBill(Request $request, BillReader $reader)
    {
        $request->validate([
            'bill_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        if (! $reader->enabled()) {
            return response()->json([
                'message' => 'Bill reading is switched off. Set BILL_READER_DRIVER and an API key in .env.',
            ], 422);
        }

        try {
            $file = $request->file('bill_image');

            $bill = $reader->read(
                (string) file_get_contents($file->getRealPath()),
                $file->getMimeType() ?: 'image/jpeg',
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($bill->toArray());
    }

    /**
     * Create one receipt line and push its accepted quantity into stock.
     * Accepted quantity is the only thing that ever becomes stock.
     */
    private function addLine(
        MaterialReceipt $receipt,
        RawMaterial $material,
        float $billQty,
        float $rate,
        float $receivedQty,
        float $damagedQty,
        ?string $remarks,
        ReceiptLinkType $linkType,
        ?int $userId,
        ?MaterialReceiptItem $parent = null,
    ): MaterialReceiptItem {
        $item = new MaterialReceiptItem([
            'material_receipt_id' => $receipt->id,
            'raw_material_id' => $material->id,
            'parent_item_id' => $parent?->id,
            'link_type' => $linkType,
            'bill_qty' => $billQty,
            'rate' => $rate,
            'received_qty' => $receivedQty,
            'damaged_qty' => $damagedQty,
            'remarks' => $remarks,
        ]);

        $item->applyUnitSnapshot($material)->recalculate();
        $item->save();

        if ((float) $item->accepted_qty > 0) {
            $this->stock->post(
                material: $material,
                type: StockTransactionType::Receipt,
                quantity: (float) $item->accepted_qty,
                date: $receipt->received_date->toDateString(),
                reference: $item,
                notes: "Received on {$receipt->receipt_no}",
                userId: $userId,
            );
        }

        return $item;
    }

    private function validateReceipt(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'supplier_vehicle_id' => [
                'nullable',
                Rule::exists('supplier_vehicles', 'id')
                    ->where('supplier_id', $request->integer('supplier_id')),
            ],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'received_date' => ['required', 'date'],
            'bill_number' => ['nullable', 'string', 'max:100'],
            'bill_date' => ['nullable', 'date'],
            'bill_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.raw_material_id' => ['required', 'exists:raw_materials,id'],
            'items.*.bill_qty' => ['required', 'numeric', 'min:0'],
            'items.*.rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.received_qty' => ['required', 'numeric', 'min:0'],
            'items.*.damaged_qty' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ], [
            'supplier_vehicle_id.exists' => 'That vehicle does not belong to the selected supplier.',
            'items.required' => 'Add at least one material line.',
        ]);

        // Damaged material is part of what arrived, so it can never exceed received.
        $validator->after(function ($validator) use ($request) {
            foreach ((array) $request->input('items', []) as $index => $row) {
                if ((float) ($row['damaged_qty'] ?? 0) > (float) ($row['received_qty'] ?? 0)) {
                    $validator->errors()->add(
                        "items.{$index}.damaged_qty",
                        'Damaged quantity cannot be more than the quantity received.',
                    );
                }
            }
        });

        return $validator->validate();
    }

    private function storeBillImage(Request $request): ?string
    {
        return $request->hasFile('bill_image')
            ? $request->file('bill_image')->store('bills/'.now()->format('Y/m'), 'public')
            : null;
    }

    /** The reader's raw suggestion, kept so a bad reading can be compared later. */
    private function decodeExtraction(Request $request): ?array
    {
        if (! $request->filled('bill_extraction')) {
            return null;
        }

        $decoded = json_decode((string) $request->input('bill_extraction'), true);

        return is_array($decoded) ? $decoded : null;
    }
}
