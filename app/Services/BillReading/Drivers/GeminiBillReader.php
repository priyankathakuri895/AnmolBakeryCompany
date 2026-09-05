<?php

namespace App\Services\BillReading\Drivers;

use App\Services\BillReading\BillReaderException;
use App\Services\BillReading\Contracts\BillReaderDriver;
use Illuminate\Support\Facades\Http;

class GeminiBillReader implements BillReaderDriver
{
    public function __construct(private array $config, private int $timeout) {}

    public function isConfigured(): bool
    {
        return filled($this->config['key'] ?? null);
    }

    public function read(string $image, string $mimeType, string $prompt): string
    {
        if (! $this->isConfigured()) {
            throw BillReaderException::notConfigured('gemini');
        }

        $url = rtrim($this->config['endpoint'], '/')
            .'/'.$this->config['model'].':generateContent';

        $response = Http::timeout($this->timeout)
            ->withHeaders(['x-goog-api-key' => $this->config['key']])
            ->post($url, [
                'contents' => [[
                    'parts' => [
                        ['inline_data' => [
                            'mime_type' => $mimeType,
                            'data' => base64_encode($image),
                        ]],
                        ['text' => $prompt],
                    ],
                ]],
                'generationConfig' => [
                    'temperature' => 0,
                    'response_mime_type' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            throw BillReaderException::apiFailed('Gemini', $response->status(), $response->body());
        }

        return (string) $response->json('candidates.0.content.parts.0.text', '');
    }
}
