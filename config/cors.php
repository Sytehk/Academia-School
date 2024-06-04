<?php

return [

    'paths' => [
        'api/*',
        'oauth/*',
        'sanctum/csrf-cookie',
        'storage/*',
    ],

    'allowed_methods' => ['*'],

    /*
    | Comma-separated list of origins allowed to call the API from a browser,
    | e.g. CORS_ALLOWED_ORIGINS="https://panel.example.com,https://school.example.com".
    | Falls back to "*" only when nothing is configured (local dev). Production
    | deployments should always set this explicitly.
    */
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*'))
    ))) ?: ['*'],

    'allowed_origins_patterns' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS_PATTERNS', ''))
    ))),

    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization', 'X-CSRF-TOKEN'],
    'max_age' => 0,

    /*
    | The API authenticates with Bearer tokens, not cross-site cookies, so
    | credentialed CORS is off by default (and "*" origins + credentials is an
    | invalid combination browsers reject). Enable only if a first-party SPA
    | uses Sanctum's cookie session against a fixed origin list above.
    */
    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', false),
];
