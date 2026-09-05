<?php

namespace App\Services\BillReading;

use Illuminate\Contracts\Support\Arrayable;

/**
 * What the reader believes it saw on a bill. Every field here is a suggestion —
 * nothing is saved until a human confirms it on the receiving form.
 */
class ExtractedBill implements Arrayable
{
    public function __construct(
        public ?string $supplierName = null,
        public ?int $supplierId = null,
        public ?string $billNumber = null,
        public ?string $billDate = null,
        public ?string $billDateRaw = null,
        public ?string $vehicleNumber = null,
        public ?int $vehicleId = null,
        /** @var array<int, array{material_id: ?int, text: string, quantity: ?float, unit: ?string}> */
        public array $items = [],
        /** @var array<int, string> */
        public array $warnings = [],
        public array $raw = [],
    ) {}

    public function toArray(): array
    {
        return [
            'supplier_name' => $this->supplierName,
            'supplier_id' => $this->supplierId,
            'bill_number' => $this->billNumber,
            'bill_date' => $this->billDate,
            'bill_date_raw' => $this->billDateRaw,
            'vehicle_number' => $this->vehicleNumber,
            'vehicle_id' => $this->vehicleId,
            'items' => $this->items,
            'warnings' => $this->warnings,
        ];
    }
}
