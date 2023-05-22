<?php

return [
    'defaults' => [
        'guard' => 'api',
    ],
    'guards' => [
        'api' => [
            'driver' => 'api'
        ],
        'cmd' => [
            'driver' => 'eloquent',
            'model'  => \App\Eloquent\User::class,
            'id' => 1,
            'school' => 'TVU'
        ],
    ],
];
