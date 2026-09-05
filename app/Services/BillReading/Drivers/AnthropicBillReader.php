<?php

namespace App\Services\BillReading\Drivers;

use App\Services\BillReading\BillReaderException;
use App\Services\BillReading\Contracts\BillReaderDriver;
use Illuminate\Support\Facades\Http;

class AnthropicBillReader implements BillReaderDriver
{
    public function __construct(private array $config, private int $timeout) {}

    public function isConfigured(): bool
    {
        return filled($this->config['key'] ?? null);
    }

    public function read(string $image, string $mimeType, string $prompt): string
    {
        if (! $this->isConfigured()) {
            throw BillReaderException::notConfigured('anthropic');
        }

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'x-api-key' => $this->config['key'],
                'anthropic-version' => $this->config['version'],
                'content-type' => 'application/json',
            ])
            ->post($this->config['endpoint'], [
                'model' => $this->config['model'],
                'max_tokens' => $this->config['max_tokens'],
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mimeType,
                                'data' => base64_encode($image),
                            ],
                        ],
                        ['type' => 'text', 'text' => $prompt],
                    ],
                ]],
            ]);

        if ($response->failed()) {
            throw BillReaderException::apiFailed('Anthropic', $response->status(), $response->body());
        }

        return (string) $response->json('content.0.text', '');
    }
}
