<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bapenda API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration untuk integrasi dengan API Bapenda
    |
    */

    'api_url' => env('BAPENDA_API_URL', 'http://10.15.36.91:7071/dpnol_data_assets'),

    'client_id' => env('BAPENDA_CLIENT_ID', '1001'),

    'username' => env('BAPENDA_USERNAME', 'samawa'),

    'secret_key' => env('BAPENDA_SECRET_KEY', ''),

    'timeout' => env('BAPENDA_TIMEOUT', 15), // Reduced timeout for faster testing

    'auto_update' => env('BAPENDA_AUTO_UPDATE', true),

    'update_on_status' => [
        '1', // Ditunda
        '2', // Disetujui
    ],

    'retry_attempts' => env('BAPENDA_RETRY_ATTEMPTS', 3),

    'retry_delay' => env('BAPENDA_RETRY_DELAY', 5), // seconds

    'log_requests' => env('BAPENDA_LOG_REQUESTS', true),

    // Mock mode untuk development/testing
    'mock_mode' => env('BAPENDA_MOCK_MODE', false),
];
