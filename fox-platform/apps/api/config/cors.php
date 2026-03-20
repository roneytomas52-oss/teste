<?php

declare(strict_types=1);

$origins = array_filter(array_map(
    'trim',
    explode(',', getenv('CORS_ALLOWED_ORIGINS') ?: implode(',', array_filter([
        getenv('ADMIN_APP_URL') ?: null,
        getenv('PARTNER_PORTAL_APP_URL') ?: null,
        getenv('DRIVER_PORTAL_APP_URL') ?: null,
        getenv('LANDING_APP_URL') ?: null,
    ])))
));

return [
    'allowed_origins' => $origins,
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With'],
    'exposed_headers' => ['Authorization'],
    'max_age' => 86400,
];
