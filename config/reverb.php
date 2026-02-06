<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reverb Server Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines the settings for the Reverb WebSocket server.
    | Adjust these settings based on your deployment environment.
    |
    */

    'host' => env('REVERB_HOST', '0.0.0.0'),

    'port' => env('REVERB_PORT', 8080),

    'scheme' => env('REVERB_SCHEME', 'http'),

    'app_id' => env('REVERB_APP_ID', env('APP_ID', 'default-app')),

    'app_key' => env('REVERB_APP_KEY', env('APP_KEY')),

    'app_secret' => env('REVERB_APP_SECRET', env('APP_SECRET')),

    /*
    |--------------------------------------------------------------------------
    | SSL Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SSL certificates for HTTPS WebSocket connections.
    | Leave null to disable SSL.
    |
    */

    'ssl' => [
        'certPath' => env('REVERB_SSL_CERT_PATH'),
        'keyPath' => env('REVERB_SSL_KEY_PATH'),
        'passphrase' => env('REVERB_SSL_PASSPHRASE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Define which origins are allowed to connect to the WebSocket server.
    | This is important for security.
    |
    */

    'allowed_origins' => [
        env('APP_URL', 'http://localhost'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ping Interval
    |--------------------------------------------------------------------------
    |
    | The interval (in seconds) at which the server sends ping messages to
    | keep connections alive.
    |
    */

    'ping_interval' => 30,

    /*
    |--------------------------------------------------------------------------
    | Maximum Message Size
    |--------------------------------------------------------------------------
    |
    | The maximum size of messages (in bytes) that can be sent through
    | the WebSocket server.
    |
    */

    'max_message_size' => 524288, // 512 KB

    /*
    |--------------------------------------------------------------------------
    | Maximum Connections
    |--------------------------------------------------------------------------
    |
    | The maximum number of concurrent WebSocket connections allowed.
    | Set to 0 for unlimited (not recommended for production).
    |
    */

    'max_connections' => 10000,

    /*
    |--------------------------------------------------------------------------
    | Throttle Configuration
    |--------------------------------------------------------------------------
    |
    | Configure throttling to prevent abuse and spam.
    |
    */

    'throttle' => [
        'enabled' => true,
        'messages_per_second' => 100,
        'ban_duration' => 60, // seconds
    ],

];
