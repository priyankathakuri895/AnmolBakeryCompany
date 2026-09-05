<?php

namespace App\Services\BillReading;

use RuntimeException;

class BillReaderException extends RuntimeException
{
    public static function notConfigured(string $driver): self
    {
        return new self("The bill reader is not configured. Set BILL_READER_DRIVER and the API key for \"{$driver}\" in .env.");
    }

    public static function apiFailed(string $driver, int $status, string $body): self
    {
        $body = str($body)->limit(300)->toString();

        return new self("The {$driver} API returned {$status}. {$body}");
    }

    public static function unreadable(): self
    {
        return new self('The bill could not be read. Please enter the delivery by hand.');
    }
}
