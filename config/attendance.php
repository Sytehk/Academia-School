<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Biometric device authentication
    |--------------------------------------------------------------------------
    |
    | POST /api/student-device-attendance accepts raw punches from a biometric /
    | QR reader. It writes attendance rows and queues WhatsApp messages to
    | parents, so it must identify the device before it does either.
    |
    | Each institute holds a secret in its `attendance_device_token` setting,
    | minted with:  php artisan attendance:device-token {institute_id}
    |
    | The device sends it as an `X-Device-Token` header (or a `device_token`
    | field), and the middleware resolves the tenant FROM that token rather than
    | trusting the QR codes in the payload.
    |
    | Set ATTENDANCE_DEVICE_STRICT=false ONLY as a temporary migration window
    | while tokens are being distributed to already-deployed devices. While it is
    | false an unauthenticated punch is still accepted, but every one is logged as
    | a warning so the remaining devices can be found. It should never be false in
    | a finished deployment.
    |
    */
    'device_strict' => (bool) env('ATTENDANCE_DEVICE_STRICT', true),

    /*
    | Rate limit for the device endpoint, as "attempts,minutes". A device syncing
    | normally posts one batched request per few minutes, so this is generous.
    */
    'device_rate_limit' => env('ATTENDANCE_DEVICE_RATE_LIMIT', '60,1'),

];
