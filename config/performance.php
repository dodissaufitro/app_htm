<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk mengoptimalkan performance aplikasi
    |
    */

    // Timeout untuk operasi database (dalam detik)
    'database_timeout' => env('DB_TIMEOUT', 30),

    // Memory limit untuk operasi berat
    'memory_limit' => env('MEMORY_LIMIT', '256M'),

    // Execution time limit
    'max_execution_time' => env('MAX_EXECUTION_TIME', 300),

    // Cache configuration
    'cache' => [
        'query_cache_ttl' => env('QUERY_CACHE_TTL', 3600), // 1 jam
        'enable_query_cache' => env('ENABLE_QUERY_CACHE', true),
    ],

    // Pagination limits
    'pagination' => [
        'default_per_page' => env('DEFAULT_PER_PAGE', 25),
        'max_per_page' => env('MAX_PER_PAGE', 100),
    ],

    // Query optimization
    'query_optimization' => [
        'enable_select_optimization' => true,
        'enable_index_hints' => true,
        'max_result_limit' => 1000,
    ],
];
