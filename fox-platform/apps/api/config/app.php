<?php

return [
    'name' => 'fox-platform-api',
    'version' => '0.1.0-phase-1',
    'locale' => 'pt_BR',
    'timezone' => 'America/Sao_Paulo',
    'runtime' => [
        'php' => '8.3',
        'framework_target' => 'laravel-12-api',
    ],
    'infrastructure' => [
        'database' => 'postgresql-16',
        'cache' => 'redis-7',
        'storage' => 'minio-s3-compatible',
    ],
    'domains' => [
        'identity',
        'stores',
        'catalog',
        'orders',
        'logistics',
        'finance',
        'marketing',
        'support',
        'analytics',
    ],
];

