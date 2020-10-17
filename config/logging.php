<?php

return [
    'default' => 'file',

    'channels' => [
        'file' => [
            'logger' => 'file',
            'path' => storage_path('logs/app.log'),
        ]
    ],
];
