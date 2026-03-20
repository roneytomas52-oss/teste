<?php

return [
    'auth' => [
        'POST /api/v1/auth/login',
        'POST /api/v1/auth/refresh',
        'POST /api/v1/auth/logout',
        'POST /api/v1/auth/forgot-password',
        'POST /api/v1/auth/reset-password',
        'GET /api/v1/auth/me',
    ],
    'admin' => [
        'GET /api/v1/admin/dashboard',
        'GET /api/v1/admin/partners',
        'GET /api/v1/admin/drivers',
        'GET /api/v1/admin/orders',
        'GET /api/v1/admin/finance/overview',
        'GET /api/v1/admin/reports/overview',
    ],
    'partner' => [
        'GET /api/v1/partner/dashboard',
        'GET /api/v1/partner/orders',
        'GET /api/v1/partner/catalog/products',
        'GET /api/v1/partner/store/profile',
        'GET /api/v1/partner/finance/summary',
    ],
    'driver' => [
        'GET /api/v1/driver/dashboard',
        'GET /api/v1/driver/profile',
        'GET /api/v1/driver/earnings',
        'GET /api/v1/driver/availability',
    ],
    'public' => [
        'GET /api/v1/public/categories',
        'GET /api/v1/public/platform-metrics',
        'POST /api/v1/public/partner-leads',
        'POST /api/v1/public/driver-leads',
    ],
];

