<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tail Sampling Configuration
    |--------------------------------------------------------------------------
    |
    | Controls when wide events are emitted. Events matching error/slow
    | conditions are always kept; remaining traffic is sampled at base_rate.
    |
    */

    'sampling' => [
        'enabled' => env('WIDE_EVENTS_ENABLED', true),
        'p99_threshold_ms' => env('WIDE_EVENTS_P99_THRESHOLD_MS', 2000),
        'base_rate' => env('WIDE_EVENTS_SAMPLE_RATE', 1.0),
        'keep_client_errors' => env('WIDE_EVENTS_KEEP_CLIENT_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | PII Filtering
    |--------------------------------------------------------------------------
    |
    | Fields in blocklist are replaced with [REDACTED].
    | Fields in hash_fields are replaced with sha256:{hash_prefix}.
    |
    */

    'pii' => [
        'blocklist' => [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'card_number',
            'cvv',
            'pan',
            'ssn',
            'social_security',
        ],
        'hash_fields' => ['email', 'phone'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    |
    | Controls what error information is included in wide events.
    |
    */

    'error' => [
        'include_stack' => env('WIDE_EVENTS_INCLUDE_STACK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Identification
    |--------------------------------------------------------------------------
    |
    | Metadata about this service instance for observability.
    |
    */

    'service_version' => env('APP_VERSION', '1.0.0'),
    'deployment_id' => env('DEPLOYMENT_ID'),
    'git_sha' => env('GIT_SHA'),
    'region' => env('APP_REGION'),
];
