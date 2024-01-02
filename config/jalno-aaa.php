<?php

return [
    'guestType' => null,
    'abilities' => [],

    'online-users-time-window' => 30, // seconds

    'routes' => [
        'enable' => true,
        'prefix' => 'api/jalno-aaa',
        'paths' => [
            'users' => [
                'only' => ['index', 'show'],
                'except' => ['store', 'update', 'destroy'],
            ],
            'types' => [
                'only' => ['index', 'show'],
                'except' => ['store', 'update', 'destroy'],
            ],
        ],
    ],

    'database' => [
        // the default connection to use for all Jalno\AAA models
        'models-connection-default' => null,

        // The specific connection name for special model.
        // This overrides default connection and also the connection defined in the model with $connection property
        'models-connection' => [
            // \Jalno\AAA\Models\Type::class => 'jalno',
            // \Jalno\AAA\Models\User::class => 'mysql',
        ],
    ],

    // This section is related to Jalno config for session.
    // You can authenticate user with the session of the Jalno.
    // Just put your Jalno configuration here to achive that purpose.
    'jalno-session' => [
        'enable' => env('JALNO_SESSION', true),

        'driver' => env('JALNO_SESSION_DRIVER', 'php'),

        'id_prefix' => env(
            'JALNO_SESSION_PREFIX',
            match (config('jalno-aaa.jalno-session.driver')) {
                'db' => '',
                'php' => 'sess_',
                'cache' => 'session-',
                default => ''
            }
        ),

        'lifetime' => env('JALNO_SESSION_LIFETIME', 120),

        'options' => [
            'php' => [
                'save_path' => env('JALNO_SESSION_DRIVER_PHP_SAVE_PATH', '/root/w/arad-branding/araduser/webserver/packages/base/storage/private/sessions'),
            ],
            'db' => [
                'connection' => env('JALNO_SESSION_DRIVER_DB_CONNECTION', 'jalno'),
                'table' => env('JALNO_SESSION_DRIVER_DB_TABLE', 'base_sessions'),
            ],
            'cache' => [
            ],
        ],

        'cookie' => [
            'name' => 'PHPSESSID',
        ],
    ],
];
