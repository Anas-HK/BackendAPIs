<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME','anas.h.khan2244@gmail.com'),
            'password' => env('MAIL_PASSWORD','ijpqrrygjcsezvne'),
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'anas.h.khan2244@gmail.com'),
        'name' => env('MAIL_FROM_NAME', 'Anas Khan'),
    ],
];
