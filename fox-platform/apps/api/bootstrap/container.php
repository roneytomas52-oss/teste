<?php

declare(strict_types=1);

use FoxPlatform\Api\Application\Auth\GetAuthenticatedUser;
use FoxPlatform\Api\Application\Auth\LoginUser;
use FoxPlatform\Api\Application\Auth\LogoutUser;
use FoxPlatform\Api\Application\Auth\RefreshToken;
use FoxPlatform\Api\Application\Auth\RequestPasswordReset;
use FoxPlatform\Api\Application\Auth\ResetPassword;
use FoxPlatform\Api\Application\Partner\AddPartnerStoreDocument;
use FoxPlatform\Api\Application\Partner\GetPartnerProfile;
use FoxPlatform\Api\Application\Partner\GetPartnerStore;
use FoxPlatform\Api\Application\Partner\ReplacePartnerStoreHours;
use FoxPlatform\Api\Application\Partner\UpdatePartnerProfile;
use FoxPlatform\Api\Application\Partner\UpdatePartnerStore;
use FoxPlatform\Api\Infrastructure\Auth\BearerTokenParser;
use FoxPlatform\Api\Infrastructure\Auth\BcryptPasswordHasher;
use FoxPlatform\Api\Infrastructure\Auth\HmacTokenIssuer;
use FoxPlatform\Api\Infrastructure\Http\Router;
use FoxPlatform\Api\Infrastructure\Persistence\DatabaseConnection;
use FoxPlatform\Api\Infrastructure\Persistence\PdoPasswordResetTokenRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoPartnerPortalRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoRefreshSessionRepository;
use FoxPlatform\Api\Infrastructure\Persistence\PdoUserRepository;
use FoxPlatform\Api\Infrastructure\Support\Clock;
use FoxPlatform\Api\Infrastructure\Support\Container;
use FoxPlatform\Api\Infrastructure\Support\UuidGenerator;
use FoxPlatform\Api\Interfaces\Http\Controllers\AuthController;
use FoxPlatform\Api\Interfaces\Http\Controllers\HealthController;
use FoxPlatform\Api\Interfaces\Http\Controllers\MeController;
use FoxPlatform\Api\Interfaces\Http\Controllers\PartnerController;
use FoxPlatform\Api\Interfaces\Http\Middleware\Authenticate;
use FoxPlatform\Api\Interfaces\Http\Middleware\CorsMiddleware;
use FoxPlatform\Api\Interfaces\Http\Middleware\JsonOnly;
use FoxPlatform\Api\Interfaces\Http\Middleware\RequireRole;
use FoxPlatform\Api\Interfaces\Http\Requests\ForgotPasswordRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\LoginRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerProfileUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreDocumentRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreHoursRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\PartnerStoreUpdateRequest;
use FoxPlatform\Api\Interfaces\Http\Requests\ResetPasswordRequest;

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

    $container->set(LoginRequest::class, static fn () => new LoginRequest());
    $container->set(ForgotPasswordRequest::class, static fn () => new ForgotPasswordRequest());
    $container->set(ResetPasswordRequest::class, static fn () => new ResetPasswordRequest());
    $container->set(PartnerProfileUpdateRequest::class, static fn () => new PartnerProfileUpdateRequest());
    $container->set(PartnerStoreUpdateRequest::class, static fn () => new PartnerStoreUpdateRequest());
    $container->set(PartnerStoreHoursRequest::class, static fn () => new PartnerStoreHoursRequest());
    $container->set(PartnerStoreDocumentRequest::class, static fn () => new PartnerStoreDocumentRequest());

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
        $c->get(PartnerProfileUpdateRequest::class),
        $c->get(PartnerStoreUpdateRequest::class),
        $c->get(PartnerStoreHoursRequest::class),
        $c->get(PartnerStoreDocumentRequest::class)
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
