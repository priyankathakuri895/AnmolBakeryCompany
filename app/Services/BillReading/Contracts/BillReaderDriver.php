<?php

namespace App\Services\BillReading\Contracts;

interface BillReaderDriver
{
    /**
     * Send the bill image to the provider and return the raw JSON string it replied with.
     *
     * @param  string  $image  Raw image bytes.
     * @param  string  $mimeType  e.g. image/jpeg
     * @param  string  $prompt  Instructions plus the known material list.
     */
    public function read(string $image, string $mimeType, string $prompt): string;

    public function isConfigured(): bool;
}
