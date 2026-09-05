<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bill reader
    |--------------------------------------------------------------------------
    |
    | Reads a photographed supplier bill and returns the supplier, bill number,
    | date and line items so the receiving form can be pre-filled. The result is
    | ALWAYS shown to staff for checking — it is never saved on its own.
    |
    | Set BILL_READER_DRIVER to "anthropic", "gemini", or "null" to turn the
    | feature off (the bill photo still uploads and is stored).
    |
    */

    'driver' => env('BILL_READER_DRIVER', 'null'),

    /*
    | Longest we wait for the API before giving up and letting staff type the
    | delivery in by hand.
    */
    'timeout' => (int) env('BILL_READER_TIMEOUT', 60),

    /*
    | Photos from a phone are far bigger than the reader needs. Downscaling
    | before upload cuts both the cost and the time per bill.
    */
    'max_image_dimension' => (int) env('BILL_READER_MAX_DIMENSION', 1600),

    'drivers' => [

        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => env('BILL_READER_MODEL', 'claude-haiku-4-5-20251001'),
            'endpoint' => 'https://api.anthropic.com/v1/messages',
            'version' => '2023-06-01',
            'max_tokens' => 2000,
        ],

        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('BILL_READER_MODEL', 'gemini-3.1-flash-lite'),
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
        ],

    ],

];
