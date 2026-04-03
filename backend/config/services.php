<?php

return [
    'postmark' => ['key' => env('POSTMARK_API_KEY')],
    'resend' => ['key' => env('RESEND_API_KEY')],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'vroom' => [
        'url' => env('VROOM_URL', 'http://localhost:3000'),
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'route' => [
        'proximity_threshold_meters' => (int) env('ROUTE_PROXIMITY_THRESHOLD_METERS', 200),
    ],

    'stripe' => [
        'prices' => [
            'basic' => env('STRIPE_PRICE_BASIC'),
            'premium' => env('STRIPE_PRICE_PREMIUM'),
        ],
    ],

    'roboflow' => [
        'api_key' => env('ROBOFLOW_API_KEY'),
        'model_id' => env('ROBOFLOW_MODEL_ID', 'mobius-v2/1'),
        'api_url' => env('ROBOFLOW_API_URL', 'https://serverless.roboflow.com'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'google' => [
        'directions_api_key' => env('GOOGLE_DIRECTIONS_API_KEY'),
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],
];
