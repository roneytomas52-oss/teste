<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Auth;

use FoxPlatform\Api\Application\Auth\DTO\AuthPayload;
use FoxPlatform\Api\Domain\Identity\RefreshSessionRepository;
use FoxPlatform\Api\Domain\Identity\TokenIssuer;
use FoxPlatform\Api\Domain\Identity\UserRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;
use FoxPlatform\Api\Infrastructure\Support\Clock;
use FoxPlatform\Api\Infrastructure\Support\UuidGenerator;

class RefreshToken
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RefreshSessionRepository $refreshSessions,
        private readonly TokenIssuer $tokenIssuer,
        private readonly UuidGenerator $uuidGenerator,
        private readonly Clock $clock,
        private readonly array $config
    ) {
    }

    public function __invoke(string $refreshToken, array $context = []): AuthPayload
    {
        $session = $this->refreshSessions->findActiveByTokenHash(hash('sha256', $refreshToken));

        if (!$session) {
            throw new ApiException(401, 'AUTH_INVALID_REFRESH', 'Refresh token invalido ou expirado.');
        }

        $user = $this->users->findById($session->userId);
        if (!$user || !$user->isActive()) {
            throw new ApiException(401, 'AUTH_INVALID_REFRESH_USER', 'Usuario vinculado ao refresh token nao esta disponivel.');
        }

        $roles = $this->users->getRolesForUser($user->id);
        $guard = $context['guard'] ?? ($roles[0]['scope'] ?? null);
        if (!$guard) {
            throw new ApiException(403, 'AUTH_GUARD_NOT_FOUND', 'Nao foi possivel determinar o portal desta sessao.');
        }

        $guardRoles = array_values(array_filter(
            $roles,
            static fn (array $role) => $role['scope'] === $guard
        ));

        if ($guardRoles === []) {
            throw new ApiException(403, 'AUTH_GUARD_FORBIDDEN', 'Este usuario nao possui acesso para o portal solicitado.');
        }

        $permissions = $this->users->getPermissionsForUser($user->id);
        $accessTtl = (int) ($this->config['tokens']['access_ttl_seconds'] ?? 900);
        $refreshTtl = (int) ($this->config['tokens']['refresh_ttl_seconds'] ?? 2592000);
        $refreshBytes = (int) ($this->config['tokens']['refresh_token_bytes'] ?? 32);
        $now = $this->clock->now();

        $this->refreshSessions->revokeById($session->id);

        $accessToken = $this->tokenIssuer->issueAccessToken([
            'sub' => $user->id,
            'name' => $user->fullName,
            'email' => $user->email,
            'guard' => $guard,
            'roles' => array_column($guardRoles, 'slug'),
            'permissions' => array_column($permissions, 'slug'),
            'iat' => $now->getTimestamp(),
            'exp' => $now->modify(sprintf('+%d seconds', $accessTtl))->getTimestamp(),
            'jti' => $this->uuidGenerator->uuid(),
        ]);

        $newRefreshToken = bin2hex(random_bytes($refreshBytes));
        $this->refreshSessions->create(
            $this->uuidGenerator->uuid(),
            $user->id,
            hash('sha256', $newRefreshToken),
            $now->modify(sprintf('+%d seconds', $refreshTtl)),
            $context['device_name'] ?? null,
            $context['ip_address'] ?? null,
            $context['user_agent'] ?? null
        );

        return new AuthPayload(
            $accessToken,
            $newRefreshToken,
            'Bearer',
            $accessTtl,
            [
                'id' => $user->id,
                'name' => $user->fullName,
                'email' => $user->email,
                'guard' => $guard,
                'roles' => array_column($guardRoles, 'slug'),
            ]
        );
    }
}
