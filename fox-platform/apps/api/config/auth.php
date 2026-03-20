<?php

return [
    'tokens' => [
        'access_ttl_seconds' => 900,
        'refresh_ttl_seconds' => 2592000,
    ],
    'guards' => [
        'admin',
        'partner',
        'driver',
    ],
    'roles' => [
        'super_admin',
        'admin_operacional',
        'admin_financeiro',
        'admin_comercial',
        'suporte',
        'partner_owner',
        'partner_manager',
        'partner_staff',
        'driver',
    ],
    'permission_modules' => [
        'dashboard',
        'orders',
        'catalog',
        'inventory',
        'marketing',
        'finance',
        'store',
        'team',
        'reviews',
        'support',
        'reports',
        'settings',
        'approvals',
    ],
    'permission_actions' => [
        'view',
        'create',
        'update',
        'delete',
        'manage',
        'approve',
        'export',
    ],
];

