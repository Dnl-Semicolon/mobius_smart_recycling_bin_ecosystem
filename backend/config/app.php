<?php

return [
    'name' => env('APP_NAME', 'Laravel'), // App name for UI/notifications
    'env' => env('APP_ENV', 'production'), // local, production
    'debug' => (bool) env('APP_DEBUG', false), // Show detailed errors
    'url' => env('APP_URL', 'http://localhost'), // Base URL for Artisan
    'timezone' => env('APP_TIMEZONE', 'Asia/Kuala_Lumpur'), // PHP date/time functions
    'locale' => env('APP_LOCALE', 'en'), // Default language
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
    'cipher' => 'AES-256-CBC', // Encryption cipher
    'key' => env('APP_KEY'), // 32-char encryption key
    'previous_keys' => [...array_filter(explode(',', (string) env('APP_PREVIOUS_KEYS', '')))],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'), // file, cache
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
