<?php declare(strict_types=1);

return [
    'default' => 'file',

    'channels' => [
        'file' => [
            'logger' => 'file',
            'path' => storage_path('logs/app.log'),
        ]
    ],
];
