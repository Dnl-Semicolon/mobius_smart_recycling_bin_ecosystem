<?php

use Illuminate\Support\Str;

return [
    'driver' => env('SESSION_DRIVER', 'database'), // file, cookie, database, memcached, redis, dynamodb, array
    'lifetime' => (int) env('SESSION_LIFETIME', 120), // Minutes
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
    'encrypt' => env('SESSION_ENCRYPT', false),
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'), // For database/redis drivers
    'table' => env('SESSION_TABLE', 'sessions'),
    'store' => env('SESSION_STORE'), // For cache-driven backends
    'lottery' => [2, 100], // Cleanup probability: 2 out of 100
    'cookie' => env('SESSION_COOKIE', Str::slug((string) env('APP_NAME', 'laravel')).'-session'),
    'path' => env('SESSION_PATH', '/'),
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE'), // HTTPS only
    'http_only' => env('SESSION_HTTP_ONLY', true), // No JS access
    'same_site' => env('SESSION_SAME_SITE', 'lax'), // lax, strict, none, null
    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),
];
