<?php

namespace App\Services\BillReading;

use App\Models\RawMaterial;
use App\Models\Supplier;
use App\Models\SupplierVehicle;
use App\Services\BillReading\Contracts\BillReaderDriver;
use App\Services\BillReading\Drivers\AnthropicBillReader;
use App\Services\BillReading\Drivers\GeminiBillReader;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Reads a photographed supplier bill and turns it into a suggestion for the
 * receiving form. Nothing this class produces is trusted: staff still count
 * the delivery and correct anything wrong before saving.
 */
class BillReader
{
    private array $config;

    /** Resolvable straight out of the container — no service provider binding needed. */
    public function __construct(?array $config = null)
    {
        $this->config = $config ?? (array) config('billreader', []);
    }

    public function enabled(): bool
    {
        $driver = $this->config['driver'] ?? 'null';

        return $driver !== 'null' && $this->driver()?->isConfigured() === true;
    }

    public function driverName(): string
    {
        return (string) ($this->config['driver'] ?? 'null');
    }

    /**
     * @param  string  $image  Raw image bytes of the bill.
     */
    public function read(string $image, string $mimeType): ExtractedBill
    {
        $driver = $this->driver();

        if (! $driver) {
            throw BillReaderException::notConfigured($this->driverName());
        }

        [$image, $mimeType] = $this->downscale($image, $mimeType);

        $materials = RawMaterial::active()->orderBy('name')->get();

        $json = $driver->read($image, $mimeType, $this->prompt($materials));

        return $this->parse($json, $materials);
    }

    private function driver(): ?BillReaderDriver
    {
        $name = $this->driverName();
        $config = $this->config['drivers'][$name] ?? null;

        if (! $config) {
            return null;
        }

        $timeout = (int) ($this->config['timeout'] ?? 60);

        return match ($name) {
            'anthropic' => new AnthropicBillReader($config, $timeout),
            'gemini' => new GeminiBillReader($config, $timeout),
            default => null,
        };
    }

    /** Build the instructions, including the materials this business actually buys. */
    private function prompt($materials): string
    {
        $list = $materials
            ->map(fn (RawMaterial $m) => sprintf(
                '- id %d: %s (1 %s = %s %s)',
                $m->id,
                $m->name,
                $m->unit_label,
                rtrim(rtrim(number_format((float) $m->unit_size, 3, '.', ''), '0'), '.'),
                $m->base_unit->label(),
            ))
            ->implode("\n");

        return <<<PROMPT
        You are reading a wholesaler's delivery bill (invoice) for a bakery in Nepal.
        The bill may be printed or handwritten, in English or Nepali, and photographed at an angle.

        Reply with ONLY a JSON object, no markdown fences, in exactly this shape:

        {
          "supplier_name": string or null,
          "bill_number": string or null,
          "bill_date": "YYYY-MM-DD" or null,
          "bill_date_raw": string or null,
          "vehicle_number": string or null,
          "items": [
            {"material_id": number or null, "text": string, "quantity": number or null, "unit": string or null}
          ],
          "warnings": [string]
        }

        Rules:
        - Match every line to one of the known materials below by meaning, not exact spelling
          (for example "maida" or "मैदा" is Flour). If a line matches nothing, set material_id
          to null and keep the original wording in "text".
        - "quantity" is the number of counting units on the bill (packets, drums, sacks).
          If the bill states a total weight instead, convert it using the unit size below and
          add a warning saying you converted it.
        - "bill_date_raw" is the date exactly as printed. Only fill "bill_date" when you are
          confident of the Gregorian (AD) date. Nepali bills often use Bikram Sambat
          (years around 2080-2090); if the date is BS or you are unsure, leave "bill_date"
          null and add a warning.
        - Never invent a value. Use null for anything you cannot read clearly, and add a
          warning explaining what was unreadable.
        - Ignore prices, taxes and totals. Only quantities matter.

        Known materials:
        {$list}
        PROMPT;
    }

    /** Turn the model's JSON into an ExtractedBill, resolving names to records we hold. */
    private function parse(string $json, $materials): ExtractedBill
    {
        $data = json_decode($this->stripFences($json), true);

        if (! is_array($data)) {
            Log::warning('Bill reader returned unparseable output.', ['output' => Str::limit($json, 500)]);

            throw BillReaderException::unreadable();
        }

        $bill = new ExtractedBill(
            supplierName: $this->nullableString($data['supplier_name'] ?? null),
            billNumber: $this->nullableString($data['bill_number'] ?? null),
            billDate: $this->normaliseDate($data['bill_date'] ?? null),
            billDateRaw: $this->nullableString($data['bill_date_raw'] ?? null),
            vehicleNumber: $this->nullableString($data['vehicle_number'] ?? null),
            warnings: array_values(array_filter(
                array_map([$this, 'nullableString'], (array) ($data['warnings'] ?? []))
            )),
            raw: $data,
        );

        $supplier = $this->matchSupplier($bill->supplierName);
        $bill->supplierId = $supplier?->id;

        if ($bill->supplierName && ! $supplier) {
            $bill->warnings[] = "\"{$bill->supplierName}\" is not in your supplier list yet — pick the right supplier or add them first.";
        }

        if ($supplier && $bill->vehicleNumber) {
            $bill->vehicleId = $this->matchVehicle($supplier, $bill->vehicleNumber)?->id;
        }

        $bill->items = $this->parseItems($data['items'] ?? [], $materials, $bill);

        if ($bill->items === []) {
            $bill->warnings[] = 'No material lines were recognised on this bill. Add them by hand below.';
        }

        return $bill;
    }

    private function parseItems(mixed $items, $materials, ExtractedBill $bill): array
    {
        $parsed = [];

        foreach ((array) $items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $text = $this->nullableString($item['text'] ?? null) ?? '';
            $materialId = isset($item['material_id']) && is_numeric($item['material_id'])
                ? (int) $item['material_id']
                : null;

            // Only trust an id that is really one of ours; otherwise fall back to the name.
            if ($materialId !== null && ! $materials->contains('id', $materialId)) {
                $materialId = null;
            }

            $materialId ??= $this->matchMaterial($text, $materials)?->id;

            if ($materialId === null && $text !== '') {
                $bill->warnings[] = "Could not match \"{$text}\" to a raw material.";
            }

            $quantity = isset($item['quantity']) && is_numeric($item['quantity'])
                ? round((float) $item['quantity'], 3)
                : null;

            if ($quantity === null && $text !== '') {
                $bill->warnings[] = "No quantity could be read for \"{$text}\".";
            }

            $parsed[] = [
                'material_id' => $materialId,
                'text' => $text,
                'quantity' => $quantity,
                'unit' => $this->nullableString($item['unit'] ?? null),
            ];
        }

        return $parsed;
    }

    private function matchSupplier(?string $name): ?Supplier
    {
        if (blank($name)) {
            return null;
        }

        $needle = $this->simplify($name);

        return Supplier::all()->first(function (Supplier $supplier) use ($needle) {
            $haystack = $this->simplify($supplier->name);

            return $haystack === $needle
                || str_contains($haystack, $needle)
                || str_contains($needle, $haystack);
        });
    }

    private function matchVehicle(Supplier $supplier, string $number): ?SupplierVehicle
    {
        $needle = $this->simplify($number);

        return $supplier->vehicles->first(
            fn (SupplierVehicle $vehicle) => $this->simplify($vehicle->vehicle_number) === $needle
        );
    }

    private function matchMaterial(string $text, $materials): ?RawMaterial
    {
        if (blank($text)) {
            return null;
        }

        $needle = $this->simplify($text);

        return $materials->first(function (RawMaterial $material) use ($needle) {
            $haystack = $this->simplify($material->name);

            return $haystack !== '' && (str_contains($needle, $haystack) || $haystack === $needle);
        });
    }

    /** Lowercase, strip punctuation and spaces, so "Puff  Ghee." matches "puff ghee". */
    private function simplify(?string $value): string
    {
        return Str::of((string) $value)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' || strtolower($value) === 'null' ? null : $value;
    }

    private function normaliseDate(mixed $value): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }

        // A Bikram Sambat year that slipped through would land far in the future.
        if ($date->year > (int) date('Y') + 1 || $date->year < 2000) {
            return null;
        }

        return $date->toDateString();
    }

    private function stripFences(string $json): string
    {
        return trim(preg_replace('/^```(?:json)?|```$/m', '', trim($json)));
    }

    /**
     * Phone photos are far larger than the reader needs; shrinking them cuts the
     * cost and the wait. Falls back to the original bytes if GD is unavailable.
     *
     * @return array{0: string, 1: string}
     */
    private function downscale(string $image, string $mimeType): array
    {
        $max = (int) ($this->config['max_image_dimension'] ?? 1600);

        if ($max <= 0 || ! extension_loaded('gd')) {
            return [$image, $mimeType];
        }

        $source = @imagecreatefromstring($image);

        if ($source === false) {
            return [$image, $mimeType];
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $longest = max($width, $height);

        if ($longest <= $max) {
            imagedestroy($source);

            return [$image, $mimeType];
        }

        $scale = $max / $longest;
        $resized = imagescale($source, (int) round($width * $scale), (int) round($height * $scale));
        imagedestroy($source);

        if ($resized === false) {
            return [$image, $mimeType];
        }

        ob_start();
        imagejpeg($resized, null, 85);
        $bytes = (string) ob_get_clean();
        imagedestroy($resized);

        return $bytes === '' ? [$image, $mimeType] : [$bytes, 'image/jpeg'];
    }
}
