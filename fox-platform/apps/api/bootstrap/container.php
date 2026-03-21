<?php

declare(strict_types=1);

use FoxPlatform\Api\Application\Auth\GetAuthenticatedUser;
use FoxPlatform\Api\Application\Auth\LoginUser;
use FoxPlatform\Api\Application\Auth\LogoutUser;
use FoxPlatform\Api\Application\Auth\RefreshToken;
use FoxPlatform\Api\Application\Auth\RequestPasswordReset;
use FoxPlatform\Api\Application\Auth\ResetPassword;
use FoxPlatform\Api\Application\Admin\GetAdminDashboard;
use FoxPlatform\Api\Application\Admin\GetAdminFinance;
use FoxPlatform\Api\Application\Admin\GetAdminDriverApprovals;
use FoxPlatform\Api\Application\Admin\GetAdminOrders;
use FoxPlatform\Api\Application\Admin\GetAdminPartnerApprovals;
use FoxPlatform\Api\Application\Admin\GetAdminSupport;
use FoxPlatform\Api\Application\Admin\ApprovePartnerApproval;
use FoxPlatform\Api\Application\Admin\RejectPartnerApproval;
use FoxPlatform\Api\Application\Admin\ApproveDriverApproval;
use FoxPlatform\Api\Application\Admin\RejectDriverApproval;
use FoxPlatform\Api\Application\Driver\GetDriverAvailability;
use FoxPlatform\Api\Application\Driver\GetDriverDashboard;
use FoxPlatform\Api\Application\Driver\GetDriverDocuments;
use FoxPlatform\Api\Application\Driver\GetDriverEarnings;
use FoxPlatform\Api\Application\Driver\GetDriverNotifications;
use FoxPlatform\Api\Application\Driver\GetDriverProfile;
use FoxPlatform\Api\Application\Driver\GetDriverSupport;
use FoxPlatform\Api\Application\Driver\GetDriverSupportThread;
use FoxPlatform\Api\Application\Driver\CreateDriverSupportTicket;
use FoxPlatform\Api\Application\Driver\MarkDriverNotificationRead;
use FoxPlatform\Api\Application\Driver\ReplyDriverSupportThread;
use FoxPlatform\Api\Application\Driver\UpdateDriverProfile;
use FoxPlatform\Api\Application\Partner\GetPartnerCatalog;
use FoxPlatform\Api\Application\Partner\AddPartnerStoreDocument;
use FoxPlatform\Api\Application\Partner\CreatePartnerTeamMember;
use FoxPlatform\Api\Application\Partner\CreatePartnerSupportTicket;
use FoxPlatform\Api\Application\Partner\CreatePartnerProduct;
use FoxPlatform\Api\Application\Partner\GetPartnerDashboard;
use FoxPlatform\Api\Application\Partner\GetPartnerFinance;
use FoxPlatform\Api\Application\Partner\GetPartnerNotifications;
use FoxPlatform\Api\Application\Partner\GetPartnerOrders;
use FoxPlatform\Api\Application\Partner\GetPartnerProfile;
use FoxPlatform\Api\Application\Partner\GetPartnerSupport;
use FoxPlatform\Api\Application\Partner\GetPartnerSupportThread;
use FoxPlatform\Api\Application\Partner\GetPartnerStore;
use FoxPlatform\Api\Application\Partner\GetPartnerTeam;
use FoxPlatform\Api\Application\Partner\MarkPartnerNotificationRead;
use FoxPlatform\Api\Application\Partner\ReplacePartnerStoreHours;
use FoxPlatform\Api\Application\Partner\ReplyPartnerSupportThread;
use FoxPlatform\Api\Application\Partner\UpdatePartnerProduct;
use FoxPlatform\Api\Application\Partner\UpdatePartnerInventory;
use FoxPlatform\Api\Application\Partner\UpdatePartnerOrderStatus;
use FoxPlatform\Api\Application\Partner\UpdatePartnerProfile;
use FoxPlatform\Api\Application\Partner\UpdatePartnerStore;
use FoxPlatform\Api\Application\Partner\UpdatePartnerTeamMember;
use FoxPlatform\Api\Application\Partner\UpdatePartnerTeamMemberStatus;
use FoxPlatform\Api\Application\Public\CreateDriverLead;
use FoxPlatform\Api\Application\Public\CreatePartnerLead;
use FoxPlatform\Api\Application\Public\GetPlatformMetrics;
use FoxPlatform\Api\Application\Public\GetPublicCategories;
use FoxPlatform\Api\Infrastructure\Auth\BearerTokenParser;
use FoxPlatform\Api\Infrastructure\Auth\BcryptPasswordHasher;
use FoxPlatform\Api\Infrastructure\Auth\HmacTokenIssuer;
use FoxPlatform\Api\Infrastructure\Http\Router;
use FoxPlatform\Api\Infrastructure\Persistence\PdoAdminOperationsRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoPartnerCatalogRepository;
use FoxPlatform\Api\Infrastructure\Persistence\DatabaseConnection;
use FoxPlatform\Api\Infrastructure\Persistence\PdoDriverPortalRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoPartnerOperationsRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoPasswordResetTokenRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoPartnerPortalRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoPublicLandingRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoRefreshSessionRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoUserRepository;
use FoxPlatform\Api\Infrastructure\Support\Clock;
use FoxPlatform\Api\Infrastructure\Support\Container;
use FoxPlatform\Api\Infrastructure\Support\UuidGenerator;
use FoxPlatform\Api\Interfaces\Http\Controllers\AdminController;
use FoxPlatform\Api\Interfaces\Http\Controllers\AuthController;
use FoxPlatform\Api\Interfaces\Http\Controllers\DriverController;
use FoxPlatform\Api\Interfaces\Http\Controllers\HealthController;
use FoxPlatform\Api\Interfaces\Http\Controllers\MeController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PartnerCatalogController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PartnerController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PartnerOperationsController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PublicLandingController;
use FoxPlatform\Api\Interfaces\Http\Middleware\Authenticate;
use FoxPlatform\Api\Interfaces\Http\Middleware\CorsMiddleware;
use FoxPlatform\Api\Interfaces\Http\Middleware\JsonOnly;
use FoxPlatform\Api\Interfaces\Http\Middleware\RequireRole;
use FoxPlatform\Api\Interfaces\Http\Requests\DriverLeadCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\ForgotPasswordRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\DriverProfileUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\LoginRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerLeadCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerProfileUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerTeamMemberStatusRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerTeamMemberUpsertRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerInventoryUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerOrderStatusUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerProductUpsertRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreDocumentRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreHoursRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\ResetPasswordRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\SupportMessageCreateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\SupportTicketCreateRequest;

return static function (string $apiRoot): Container {
    $container = new Container();

    $container->set('config.app', require $apiRoot . '/config/app.php');
    $container->set('config.auth', require $apiRoot . '/config/auth.php');
    $container->set('config.database', require $apiRoot . '/config/database.php');
    $container->set('config.cors', require $apiRoot . '/config/cors.php');

    $container->set('router', static fn () => new Router());

    $container->set(Clock::class, static fn () => new Clock());
    $container->set(UuidGenerator::class, static fn () => new UuidGenerator());
    $container->set(DatabaseConnection::class, static fn (Container $c) => new DatabaseConnection($c->get('config.database')));
    $container->set(BcryptPasswordHasher::class, static fn () => new BcryptPasswordHasher());
    $container->set(BearerTokenParser::class, static fn () => new BearerTokenParser());
    $container->set(HmacTokenIssuer::class, static fn (Container $c) => new HmacTokenIssuer($c->get('config.auth')));

    $container->set(PdoUserRepository::class, static fn (Container $c) => new PdoUserRepository($c->get(DatabaseConnection::class)->pdo()));
    $container->set(PdoRefreshSessionRepository::class, static fn (Container $c) => new PdoRefreshSessionRepository($c->get(DatabaseConnection::class)->pdo()));
    $container->set(PdoPasswordResetTokenRepository::class, static fn (Container $c) => new PdoPasswordResetTokenRepository($c->get(DatabaseConnection::class)->pdo()));
    $container->set(PdoPartnerPortalRepository::class, static fn (Container $c) => new PdoPartnerPortalRepository($c->get(DatabaseConnection::class)->pdo()));
    $container->set(PdoPartnerCatalogRepository::class, static fn (Container $c) => new PdoPartnerCatalogRepository($c->get(DatabaseConnection::class)->pdo()));
    $container->set(PdoPartnerOperationsRepository::class, static fn (Container $c) => new PdoPartnerOperationsRepository($c->get(DatabaseConnection::class)->pdo()));
    $container->set(PdoAdminOperationsRepository::class, static fn (Container $c) => new PdoAdminOperationsRepository($c->get(DatabaseConnection::class)->pdo()));
    $container->set(PdoDriverPortalRepository::class, static fn (Container $c) => new PdoDriverPortalRepository($c->get(DatabaseConnection::class)->pdo()));
    $container->set(PdoPublicLandingRepository::class, static fn (Container $c) => new PdoPublicLandingRepository($c->get(DatabaseConnection::class)->pdo()));

    $container->set(LoginUser::class, static fn (Container $c) => new LoginUser(
        $c->get(PdoUserRepository::class),
        $c->get(PdoRefreshSessionRepository::class),
        $c->get(BcryptPasswordHasher::class),
        $c->get(HmacTokenIssuer::class),
        $c->get(UuidGenerator::class),
        $c->get(Clock::class),
        $c->get('config.auth')
    ));
    $container->set(LogoutUser::class, static fn (Container $c) => new LogoutUser(
        $c->get(PdoRefreshSessionRepository::class)
    ));
    $container->set(RefreshToken::class, static fn (Container $c) => new RefreshToken(
        $c->get(PdoUserRepository::class),
        $c->get(PdoRefreshSessionRepository::class),
        $c->get(HmacTokenIssuer::class),
        $c->get(UuidGenerator::class),
        $c->get(Clock::class),
        $c->get('config.auth')
    ));
    $container->set(GetAuthenticatedUser::class, static fn (Container $c) => new GetAuthenticatedUser(
        $c->get(PdoUserRepository::class)
    ));
    $container->set(RequestPasswordReset::class, static fn (Container $c) => new RequestPasswordReset(
        $c->get(PdoUserRepository::class),
        $c->get(PdoPasswordResetTokenRepository::class),
        $c->get(UuidGenerator::class),
        $c->get(Clock::class),
        $c->get('config.auth')
    ));
    $container->set(ResetPassword::class, static fn (Container $c) => new ResetPassword(
        $c->get(PdoUserRepository::class),
        $c->get(PdoPasswordResetTokenRepository::class),
        $c->get(PdoRefreshSessionRepository::class),
        $c->get(BcryptPasswordHasher::class)
    ));
    $container->set(GetPartnerProfile::class, static fn (Container $c) => new GetPartnerProfile(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(UpdatePartnerProfile::class, static fn (Container $c) => new UpdatePartnerProfile(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(GetPartnerStore::class, static fn (Container $c) => new GetPartnerStore(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(UpdatePartnerStore::class, static fn (Container $c) => new UpdatePartnerStore(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(ReplacePartnerStoreHours::class, static fn (Container $c) => new ReplacePartnerStoreHours(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(AddPartnerStoreDocument::class, static fn (Container $c) => new AddPartnerStoreDocument(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(GetPartnerCatalog::class, static fn (Container $c) => new GetPartnerCatalog(
        $c->get(PdoPartnerCatalogRepository::class)
    ));
    $container->set(CreatePartnerProduct::class, static fn (Container $c) => new CreatePartnerProduct(
        $c->get(PdoPartnerCatalogRepository::class)
    ));
    $container->set(UpdatePartnerProduct::class, static fn (Container $c) => new UpdatePartnerProduct(
        $c->get(PdoPartnerCatalogRepository::class)
    ));
    $container->set(UpdatePartnerInventory::class, static fn (Container $c) => new UpdatePartnerInventory(
        $c->get(PdoPartnerCatalogRepository::class)
    ));
    $container->set(GetPartnerDashboard::class, static fn (Container $c) => new GetPartnerDashboard(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(GetPartnerFinance::class, static fn (Container $c) => new GetPartnerFinance(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(GetPartnerOrders::class, static fn (Container $c) => new GetPartnerOrders(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(GetPartnerSupport::class, static fn (Container $c) => new GetPartnerSupport(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(GetPartnerSupportThread::class, static fn (Container $c) => new GetPartnerSupportThread(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(CreatePartnerSupportTicket::class, static fn (Container $c) => new CreatePartnerSupportTicket(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(ReplyPartnerSupportThread::class, static fn (Container $c) => new ReplyPartnerSupportThread(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(GetPartnerNotifications::class, static fn (Container $c) => new GetPartnerNotifications(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(MarkPartnerNotificationRead::class, static fn (Container $c) => new MarkPartnerNotificationRead(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(UpdatePartnerOrderStatus::class, static fn (Container $c) => new UpdatePartnerOrderStatus(
        $c->get(PdoPartnerOperationsRepository::class)
    ));
    $container->set(GetPartnerTeam::class, static fn (Container $c) => new GetPartnerTeam(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(CreatePartnerTeamMember::class, static fn (Container $c) => new CreatePartnerTeamMember(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(UpdatePartnerTeamMember::class, static fn (Container $c) => new UpdatePartnerTeamMember(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(UpdatePartnerTeamMemberStatus::class, static fn (Container $c) => new UpdatePartnerTeamMemberStatus(
        $c->get(PdoPartnerPortalRepository::class)
    ));
    $container->set(GetAdminDashboard::class, static fn (Container $c) => new GetAdminDashboard(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(GetAdminFinance::class, static fn (Container $c) => new GetAdminFinance(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(GetAdminOrders::class, static fn (Container $c) => new GetAdminOrders(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(GetAdminPartnerApprovals::class, static fn (Container $c) => new GetAdminPartnerApprovals(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(GetAdminDriverApprovals::class, static fn (Container $c) => new GetAdminDriverApprovals(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(GetAdminSupport::class, static fn (Container $c) => new GetAdminSupport(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(ApprovePartnerApproval::class, static fn (Container $c) => new ApprovePartnerApproval(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(RejectPartnerApproval::class, static fn (Container $c) => new RejectPartnerApproval(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(ApproveDriverApproval::class, static fn (Container $c) => new ApproveDriverApproval(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(RejectDriverApproval::class, static fn (Container $c) => new RejectDriverApproval(
        $c->get(PdoAdminOperationsRepository::class)
    ));
    $container->set(GetDriverDashboard::class, static fn (Container $c) => new GetDriverDashboard(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(GetDriverProfile::class, static fn (Container $c) => new GetDriverProfile(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(UpdateDriverProfile::class, static fn (Container $c) => new UpdateDriverProfile(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(GetDriverEarnings::class, static fn (Container $c) => new GetDriverEarnings(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(GetDriverAvailability::class, static fn (Container $c) => new GetDriverAvailability(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(GetDriverDocuments::class, static fn (Container $c) => new GetDriverDocuments(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(GetDriverSupport::class, static fn (Container $c) => new GetDriverSupport(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(GetDriverSupportThread::class, static fn (Container $c) => new GetDriverSupportThread(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(CreateDriverSupportTicket::class, static fn (Container $c) => new CreateDriverSupportTicket(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(ReplyDriverSupportThread::class, static fn (Container $c) => new ReplyDriverSupportThread(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(GetDriverNotifications::class, static fn (Container $c) => new GetDriverNotifications(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(MarkDriverNotificationRead::class, static fn (Container $c) => new MarkDriverNotificationRead(
        $c->get(PdoDriverPortalRepository::class)
    ));
    $container->set(GetPublicCategories::class, static fn (Container $c) => new GetPublicCategories(
        $c->get(PdoPublicLandingRepository::class)
    ));
    $container->set(GetPlatformMetrics::class, static fn (Container $c) => new GetPlatformMetrics(
        $c->get(PdoPublicLandingRepository::class)
    ));
    $container->set(CreatePartnerLead::class, static fn (Container $c) => new CreatePartnerLead(
        $c->get(PdoPublicLandingRepository::class)
    ));
    $container->set(CreateDriverLead::class, static fn (Container $c) => new CreateDriverLead(
        $c->get(PdoPublicLandingRepository::class)
    ));

    $container->set(LoginRequest::class, static fn () => new LoginRequest());
    $container->set(ForgotPasswordRequest::class, static fn () => new ForgotPasswordRequest());
    $container->set(DriverLeadCreateRequest::class, static fn () => new DriverLeadCreateRequest());
    $container->set(DriverProfileUpdateRequest::class, static fn () => new DriverProfileUpdateRequest());
    $container->set(ResetPasswordRequest::class, static fn () => new ResetPasswordRequest());
    $container->set(PartnerLeadCreateRequest::class, static fn () => new PartnerLeadCreateRequest());
    $container->set(PartnerProfileUpdateRequest::class, static fn () => new PartnerProfileUpdateRequest());
    $container->set(PartnerInventoryUpdateRequest::class, static fn () => new PartnerInventoryUpdateRequest());
    $container->set(PartnerOrderStatusUpdateRequest::class, static fn () => new PartnerOrderStatusUpdateRequest());
    $container->set(PartnerProductUpsertRequest::class, static fn () => new PartnerProductUpsertRequest());
    $container->set(PartnerStoreUpdateRequest::class, static fn () => new PartnerStoreUpdateRequest());
    $container->set(PartnerStoreHoursRequest::class, static fn () => new PartnerStoreHoursRequest());
    $container->set(PartnerStoreDocumentRequest::class, static fn () => new PartnerStoreDocumentRequest());
    $container->set(PartnerTeamMemberUpsertRequest::class, static fn () => new PartnerTeamMemberUpsertRequest());
    $container->set(PartnerTeamMemberStatusRequest::class, static fn () => new PartnerTeamMemberStatusRequest());
    $container->set(SupportMessageCreateRequest::class, static fn () => new SupportMessageCreateRequest());
    $container->set(SupportTicketCreateRequest::class, static fn () => new SupportTicketCreateRequest());

    $container->set(HealthController::class, static fn (Container $c) => new HealthController($c->get('config.app')));
    $container->set(AuthController::class, static fn (Container $c) => new AuthController(
        $c->get(LoginUser::class),
        $c->get(LogoutUser::class),
        $c->get(RefreshToken::class),
        $c->get(RequestPasswordReset::class),
        $c->get(ResetPassword::class),
        $c->get(LoginRequest::class),
        $c->get(ForgotPasswordRequest::class),
        $c->get(ResetPasswordRequest::class)
    ));
    $container->set(MeController::class, static fn (Container $c) => new MeController(
        $c->get(GetAuthenticatedUser::class)
    ));
    $container->set(PartnerController::class, static fn (Container $c) => new PartnerController(
        $c->get(GetPartnerProfile::class),
        $c->get(UpdatePartnerProfile::class),
        $c->get(GetPartnerStore::class),
        $c->get(UpdatePartnerStore::class),
        $c->get(ReplacePartnerStoreHours::class),
        $c->get(AddPartnerStoreDocument::class),
        $c->get(GetPartnerTeam::class),
        $c->get(CreatePartnerTeamMember::class),
        $c->get(UpdatePartnerTeamMember::class),
        $c->get(UpdatePartnerTeamMemberStatus::class),
        $c->get(PartnerProfileUpdateRequest::class),
        $c->get(PartnerStoreUpdateRequest::class),
        $c->get(PartnerStoreHoursRequest::class),
        $c->get(PartnerStoreDocumentRequest::class),
        $c->get(PartnerTeamMemberUpsertRequest::class),
        $c->get(PartnerTeamMemberStatusRequest::class)
    ));
    $container->set(PartnerCatalogController::class, static fn (Container $c) => new PartnerCatalogController(
        $c->get(GetPartnerCatalog::class),
        $c->get(CreatePartnerProduct::class),
        $c->get(UpdatePartnerProduct::class),
        $c->get(UpdatePartnerInventory::class),
        $c->get(PartnerProductUpsertRequest::class),
        $c->get(PartnerInventoryUpdateRequest::class)
    ));
    $container->set(PartnerOperationsController::class, static fn (Container $c) => new PartnerOperationsController(
        $c->get(GetPartnerDashboard::class),
        $c->get(GetPartnerFinance::class),
        $c->get(GetPartnerOrders::class),
        $c->get(GetPartnerSupport::class),
        $c->get(GetPartnerSupportThread::class),
        $c->get(CreatePartnerSupportTicket::class),
        $c->get(ReplyPartnerSupportThread::class),
        $c->get(GetPartnerNotifications::class),
        $c->get(MarkPartnerNotificationRead::class),
        $c->get(UpdatePartnerOrderStatus::class),
        $c->get(PartnerOrderStatusUpdateRequest::class),
        $c->get(SupportTicketCreateRequest::class),
        $c->get(SupportMessageCreateRequest::class)
    ));
    $container->set(AdminController::class, static fn (Container $c) => new AdminController(
        $c->get(GetAdminDashboard::class),
        $c->get(GetAdminFinance::class),
        $c->get(GetAdminOrders::class),
        $c->get(GetAdminPartnerApprovals::class),
        $c->get(GetAdminDriverApprovals::class),
        $c->get(GetAdminSupport::class),
        $c->get(ApprovePartnerApproval::class),
        $c->get(RejectPartnerApproval::class),
        $c->get(ApproveDriverApproval::class),
        $c->get(RejectDriverApproval::class)
    ));
    $container->set(DriverController::class, static fn (Container $c) => new DriverController(
        $c->get(GetDriverDashboard::class),
        $c->get(GetDriverProfile::class),
        $c->get(UpdateDriverProfile::class),
        $c->get(GetDriverEarnings::class),
        $c->get(GetDriverAvailability::class),
        $c->get(GetDriverDocuments::class),
        $c->get(GetDriverSupport::class),
        $c->get(GetDriverSupportThread::class),
        $c->get(CreateDriverSupportTicket::class),
        $c->get(ReplyDriverSupportThread::class),
        $c->get(GetDriverNotifications::class),
        $c->get(MarkDriverNotificationRead::class),
        $c->get(DriverProfileUpdateRequest::class),
        $c->get(SupportTicketCreateRequest::class),
        $c->get(SupportMessageCreateRequest::class)
    ));
    $container->set(PublicLandingController::class, static fn (Container $c) => new PublicLandingController(
        $c->get(GetPublicCategories::class),
        $c->get(GetPlatformMetrics::class),
        $c->get(CreatePartnerLead::class),
        $c->get(CreateDriverLead::class),
        $c->get(PartnerLeadCreateRequest::class),
        $c->get(DriverLeadCreateRequest::class)
    ));

    $container->set('middleware.json', static fn () => new JsonOnly());
    $container->set('middleware.auth', static fn (Container $c) => new Authenticate(
        $c->get(BearerTokenParser::class),
        $c->get(HmacTokenIssuer::class),
        $c->get(PdoUserRepository::class)
    ));
    $container->set('middleware.role', static fn () => new RequireRole());
    $container->set('middleware.cors', static fn (Container $c) => new CorsMiddleware(
        $c->get('config.cors')
    ));

    return $container;
};
