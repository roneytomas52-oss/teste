<?php

declare(strict_types=1);

use FoxPlatform\Api\Infrastructure\Http\Router;
use FoxPlatform\Api\Infrastructure\Support\Container;
use FoxPlatform\Api\Interfaces\Http\Controllers\AdminController;
use FoxPlatform\Api\Interfaces\Http\Controllers\AuthController;
use FoxPlatform\Api\Interfaces\Http\Controllers\DriverController;
use FoxPlatform\Api\Interfaces\Http\Controllers\HealthController;
use FoxPlatform\Api\Interfaces\Http\Controllers\MeController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PartnerCatalogController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PartnerController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PartnerOperationsController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PublicLandingController;

return static function (Router $router, Container $container): void {
    $router->get('/health', [HealthController::class, 'show'], ['cors']);

    $router->post('/api/v1/auth/login', [AuthController::class, 'login'], ['cors', 'json']);
    $router->post('/api/v1/auth/logout', [AuthController::class, 'logout'], ['cors', 'json']);
    $router->post('/api/v1/auth/refresh', [AuthController::class, 'refresh'], ['cors', 'json']);
    $router->post('/api/v1/auth/forgot-password', [AuthController::class, 'forgotPassword'], ['cors', 'json']);
    $router->post('/api/v1/auth/reset-password', [AuthController::class, 'resetPassword'], ['cors', 'json']);
    $router->get('/api/v1/auth/me', [MeController::class, 'show'], ['cors', 'auth']);

    $router->get('/api/v1/public/categories', [PublicLandingController::class, 'categories'], ['cors']);
    $router->get('/api/v1/public/platform-metrics', [PublicLandingController::class, 'metrics'], ['cors']);
    $router->get('/api/v1/public/stores', [PublicLandingController::class, 'stores'], ['cors']);
    $router->get('/api/v1/public/stores/{store_id}', [PublicLandingController::class, 'storeDetail'], ['cors']);
    $router->post('/api/v1/public/orders', [PublicLandingController::class, 'createOrder'], ['cors', 'json']);
    $router->post('/api/v1/public/partner-leads', [PublicLandingController::class, 'createPartnerLeadAction'], ['cors', 'json']);
    $router->post('/api/v1/public/driver-leads', [PublicLandingController::class, 'createDriverLeadAction'], ['cors', 'json']);

    $router->get('/api/v1/partner/profile', [PartnerController::class, 'profile'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:dashboard.view']);
    $router->put('/api/v1/partner/profile', [PartnerController::class, 'updateProfile'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager', 'permission:store.manage']);
    $router->get('/api/v1/partner/store', [PartnerController::class, 'store'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:dashboard.view']);
    $router->put('/api/v1/partner/store', [PartnerController::class, 'updateStore'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager', 'permission:store.manage']);
    $router->put('/api/v1/partner/store/hours', [PartnerController::class, 'updateStoreHours'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager', 'permission:store.manage']);
    $router->post('/api/v1/partner/store/documents', [PartnerController::class, 'addStoreDocument'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager', 'permission:store.manage']);
    $router->get('/api/v1/partner/team', [PartnerController::class, 'team'], ['cors', 'auth', 'role:partner_owner,partner_manager', 'permission:team.manage']);
    $router->post('/api/v1/partner/team', [PartnerController::class, 'createTeamMember'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager', 'permission:team.manage']);
    $router->put('/api/v1/partner/team/{member_id}', [PartnerController::class, 'updateTeamMember'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager', 'permission:team.manage']);
    $router->put('/api/v1/partner/team/{member_id}/status', [PartnerController::class, 'updateTeamMemberStatus'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager', 'permission:team.manage']);
    $router->get('/api/v1/partner/dashboard', [PartnerOperationsController::class, 'dashboard'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:dashboard.view']);
    $router->get('/api/v1/partner/finance/summary', [PartnerOperationsController::class, 'finance'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:finance.view']);
    $router->get('/api/v1/partner/orders', [PartnerOperationsController::class, 'orders'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:orders.manage']);
    $router->get('/api/v1/partner/orders/{order_id}', [PartnerOperationsController::class, 'orderDetail'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:orders.manage']);
    $router->put('/api/v1/partner/orders/{order_id}/status', [PartnerOperationsController::class, 'updateOrderStatus'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager,partner_staff', 'permission:orders.manage']);
    $router->get('/api/v1/partner/support', [PartnerOperationsController::class, 'support'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:support.manage']);
    $router->get('/api/v1/partner/support/{ticket_id}', [PartnerOperationsController::class, 'supportThread'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:support.manage']);
    $router->post('/api/v1/partner/support/tickets', [PartnerOperationsController::class, 'createSupportTicket'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager,partner_staff', 'permission:support.manage']);
    $router->post('/api/v1/partner/support/{ticket_id}/messages', [PartnerOperationsController::class, 'replySupportThread'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager,partner_staff', 'permission:support.manage']);
    $router->get('/api/v1/partner/notifications', [PartnerOperationsController::class, 'notifications'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:dashboard.view']);
    $router->post('/api/v1/partner/notifications/{notification_id}/read', [PartnerOperationsController::class, 'markNotificationRead'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:dashboard.view']);
    $router->get('/api/v1/partner/catalog/products', [PartnerCatalogController::class, 'catalog'], ['cors', 'auth', 'role:partner_owner,partner_manager,partner_staff', 'permission:catalog.manage,inventory.manage']);
    $router->post('/api/v1/partner/catalog/products', [PartnerCatalogController::class, 'createProduct'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager,partner_staff', 'permission:catalog.manage']);
    $router->put('/api/v1/partner/catalog/products/{product_id}', [PartnerCatalogController::class, 'updateProduct'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager,partner_staff', 'permission:catalog.manage']);
    $router->put('/api/v1/partner/catalog/products/{product_id}/inventory', [PartnerCatalogController::class, 'updateInventory'], ['cors', 'auth', 'json', 'role:partner_owner,partner_manager,partner_staff', 'permission:inventory.manage']);

    $router->get('/api/v1/admin/dashboard', [AdminController::class, 'dashboard'], ['cors', 'auth', 'role:super_admin']);
    $router->get('/api/v1/admin/analytics', [AdminController::class, 'analytics'], ['cors', 'auth', 'role:super_admin', 'permission:reports.view']);
    $router->get('/api/v1/admin/access', [AdminController::class, 'access'], ['cors', 'auth', 'role:super_admin', 'permission:settings.manage']);
    $router->post('/api/v1/admin/access/members', [AdminController::class, 'createAccessMember'], ['cors', 'auth', 'role:super_admin', 'permission:settings.manage', 'json']);
    $router->put('/api/v1/admin/access/members/{member_id}', [AdminController::class, 'updateAccessMember'], ['cors', 'auth', 'role:super_admin', 'permission:settings.manage', 'json']);
    $router->put('/api/v1/admin/access/members/{member_id}/status', [AdminController::class, 'updateAccessMemberStatus'], ['cors', 'auth', 'role:super_admin', 'permission:settings.manage', 'json']);
    $router->get('/api/v1/admin/finance/overview', [AdminController::class, 'finance'], ['cors', 'auth', 'role:super_admin']);
    $router->get('/api/v1/admin/reports', [AdminController::class, 'reports'], ['cors', 'auth', 'role:super_admin', 'permission:reports.view']);
    $router->get('/api/v1/admin/notifications', [AdminController::class, 'notifications'], ['cors', 'auth', 'role:super_admin', 'permission:dashboard.view']);
    $router->post('/api/v1/admin/notifications/{notification_id}/read', [AdminController::class, 'markNotificationRead'], ['cors', 'auth', 'role:super_admin', 'permission:dashboard.view']);
    $router->get('/api/v1/admin/orders', [AdminController::class, 'orders'], ['cors', 'auth', 'role:super_admin']);
    $router->get('/api/v1/admin/orders/{order_id}', [AdminController::class, 'orderDetail'], ['cors', 'auth', 'role:super_admin']);
    $router->put('/api/v1/admin/orders/{order_id}/status', [AdminController::class, 'updateOrderStatus'], ['cors', 'auth', 'role:super_admin', 'json']);
    $router->post('/api/v1/admin/orders/{order_id}/note', [AdminController::class, 'addOrderNote'], ['cors', 'auth', 'role:super_admin', 'json']);
    $router->get('/api/v1/admin/approvals/partners', [AdminController::class, 'partnerApprovals'], ['cors', 'auth', 'role:super_admin']);
    $router->get('/api/v1/admin/approvals/partners/{partner_id}', [AdminController::class, 'partnerApprovalDetail'], ['cors', 'auth', 'role:super_admin']);
    $router->put('/api/v1/admin/approvals/partners/{partner_id}/decision', [AdminController::class, 'resolvePartnerApproval'], ['cors', 'auth', 'role:super_admin', 'json']);
    $router->get('/api/v1/admin/approvals/drivers', [AdminController::class, 'driverApprovals'], ['cors', 'auth', 'role:super_admin']);
    $router->get('/api/v1/admin/approvals/drivers/{driver_id}', [AdminController::class, 'driverApprovalDetail'], ['cors', 'auth', 'role:super_admin']);
    $router->put('/api/v1/admin/approvals/drivers/{driver_id}/decision', [AdminController::class, 'resolveDriverApproval'], ['cors', 'auth', 'role:super_admin', 'json']);
    $router->post('/api/v1/admin/approvals/partners/{partner_id}/approve', [AdminController::class, 'approvePartner'], ['cors', 'auth', 'role:super_admin']);
    $router->post('/api/v1/admin/approvals/partners/{partner_id}/reject', [AdminController::class, 'rejectPartner'], ['cors', 'auth', 'role:super_admin']);
    $router->post('/api/v1/admin/approvals/drivers/{driver_id}/approve', [AdminController::class, 'approveDriver'], ['cors', 'auth', 'role:super_admin']);
    $router->post('/api/v1/admin/approvals/drivers/{driver_id}/reject', [AdminController::class, 'rejectDriver'], ['cors', 'auth', 'role:super_admin']);
    $router->get('/api/v1/admin/support/queue', [AdminController::class, 'support'], ['cors', 'auth', 'role:super_admin']);
    $router->get('/api/v1/admin/support/{ticket_id}', [AdminController::class, 'supportThread'], ['cors', 'auth', 'role:super_admin']);
    $router->post('/api/v1/admin/support/{ticket_id}/messages', [AdminController::class, 'replySupportThread'], ['cors', 'auth', 'role:super_admin', 'json']);
    $router->put('/api/v1/admin/support/{ticket_id}/status', [AdminController::class, 'updateSupportTicketStatus'], ['cors', 'auth', 'role:super_admin', 'json']);
    $router->get('/api/v1/admin/settings', [AdminController::class, 'settings'], ['cors', 'auth', 'role:super_admin', 'permission:settings.manage']);
    $router->put('/api/v1/admin/settings', [AdminController::class, 'updateSettings'], ['cors', 'auth', 'json', 'role:super_admin', 'permission:settings.manage']);

    $router->get('/api/v1/driver/dashboard', [DriverController::class, 'dashboard'], ['cors', 'auth', 'role:driver']);
    $router->get('/api/v1/driver/profile', [DriverController::class, 'profile'], ['cors', 'auth', 'role:driver']);
    $router->put('/api/v1/driver/profile', [DriverController::class, 'updateProfile'], ['cors', 'auth', 'json', 'role:driver']);
    $router->get('/api/v1/driver/earnings', [DriverController::class, 'earnings'], ['cors', 'auth', 'role:driver']);
    $router->get('/api/v1/driver/availability', [DriverController::class, 'availability'], ['cors', 'auth', 'role:driver']);
    $router->get('/api/v1/driver/documents', [DriverController::class, 'documents'], ['cors', 'auth', 'role:driver']);
    $router->get('/api/v1/driver/support', [DriverController::class, 'support'], ['cors', 'auth', 'role:driver']);
    $router->get('/api/v1/driver/support/{ticket_id}', [DriverController::class, 'supportThread'], ['cors', 'auth', 'role:driver']);
    $router->post('/api/v1/driver/support/tickets', [DriverController::class, 'createSupportTicket'], ['cors', 'auth', 'json', 'role:driver']);
    $router->post('/api/v1/driver/support/{ticket_id}/messages', [DriverController::class, 'replySupportThread'], ['cors', 'auth', 'json', 'role:driver']);
    $router->get('/api/v1/driver/notifications', [DriverController::class, 'notifications'], ['cors', 'auth', 'role:driver']);
    $router->post('/api/v1/driver/notifications/{notification_id}/read', [DriverController::class, 'markNotificationRead'], ['cors', 'auth', 'role:driver']);
};
