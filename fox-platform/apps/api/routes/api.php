<?php

declare(strict_types=1);

use FoxPlatform\Api\Infrastructure\Http\Router;
use FoxPlatform\Api\Infrastructure\Support\Container;
use FoxPlatform\Api\Interfaces\Http\Controllers\AuthController;
use FoxPlatform\Api\Interfaces\Http\Controllers\HealthController;
use FoxPlatform\Api\Interfaces\Http\Controllers\MeController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PartnerController;

return static function (Router $router, Container $container): void {
    $router->get('/health', [HealthController::class, 'show'], ['cors']);

    $router->post('/api/v1/auth/login', [AuthController::class, 'login'], ['cors', 'json']);
    $router->post('/api/v1/auth/logout', [AuthController::class, 'logout'], ['cors', 'json']);
    $router->post('/api/v1/auth/refresh', [AuthController::class, 'refresh'], ['cors', 'json']);
    $router->post('/api/v1/auth/forgot-password', [AuthController::class, 'forgotPassword'], ['cors', 'json']);
    $router->post('/api/v1/auth/reset-password', [AuthController::class, 'resetPassword'], ['cors', 'json']);
    $router->get('/api/v1/auth/me', [MeController::class, 'show'], ['cors', 'auth']);

    $router->get('/api/v1/partner/profile', [PartnerController::class, 'profile'], ['cors', 'auth', 'role:partner_owner']);
    $router->put('/api/v1/partner/profile', [PartnerController::class, 'updateProfile'], ['cors', 'auth', 'json', 'role:partner_owner']);
    $router->get('/api/v1/partner/store', [PartnerController::class, 'store'], ['cors', 'auth', 'role:partner_owner']);
    $router->put('/api/v1/partner/store', [PartnerController::class, 'updateStore'], ['cors', 'auth', 'json', 'role:partner_owner']);
    $router->put('/api/v1/partner/store/hours', [PartnerController::class, 'updateStoreHours'], ['cors', 'auth', 'json', 'role:partner_owner']);
    $router->post('/api/v1/partner/store/documents', [PartnerController::class, 'addStoreDocument'], ['cors', 'auth', 'json', 'role:partner_owner']);
};
