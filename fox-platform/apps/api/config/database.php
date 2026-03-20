<?php

declare(strict_types=1);

return [
    'driver' => getenv('DB_CONNECTION') ?: 'pgsql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('DB_PORT') ?: 5432),
    'database' => getenv('DB_DATABASE') ?: 'fox_platform',
    'username' => getenv('DB_USERNAME') ?: 'fox_platform',
    'password' => getenv('DB_PASSWORD') ?: 'fox_platform',
    'charset' => getenv('DB_CHARSET') ?: 'utf8',
    'sslmode' => getenv('DB_SSLMODE') ?: 'prefer',
];
