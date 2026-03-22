<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Auth;

use FoxPlatform\Api\Application\Auth\DTO\AuthPayload;
use FoxPlatform\Api\Application\Auth\DTO\LoginInput;
use FoxPlatform\Api\Domain\Identity\PasswordHasher;
use FoxPlatform\Api\Domain\Identity\RefreshSessionRepository;
use FoxPlatform\Api\Domain\Identity\TokenIssuer;
use FoxPlatform\Api\Domain\Identity\UserRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;
use FoxPlatform\Api\Infrastructure\Support\Clock;
use FoxPlatform\Api\Infrastructure\Support\UuidGenerator;

class LoginUser
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly RefreshSessionRepository $refreshSessions,
        private readonly PasswordHasher $passwordHasher,
        private readonly TokenIssuer $tokenIssuer,
        private readonly UuidGenerator $uuidGenerator,
        private readonly Clock $clock,
        private readonly array $config
    ) {
    }

    public function __invoke(LoginInput $input, array $context = []): AuthPayload
    {
        $user = $this->users->findByEmail($input->email);

        if (!$user || !$this->passwordHasher->verify($input->password, $user->passwordHash)) {
            throw new ApiException(401, 'AUTH_INVALID_CREDENTIALS', 'Credenciais invalidas.');
        }

        if (!$user->isActive()) {
            throw new ApiException(403, 'AUTH_USER_NOT_ACTIVE', 'A conta ainda nao esta ativa para acesso.');
        }

        $roles = $this->users->getRolesForUser($user->id);
        $guardRoles = array_values(array_filter(
            $roles,
            static fn (array $role) => $role['scope'] === $input->guard
        ));

        if ($guardRoles === []) {
            throw new ApiException(403, 'AUTH_GUARD_FORBIDDEN', 'Este usuario nao possui acesso para o portal solicitado.');
        }

        $permissions = $this->users->getPermissionsForUser($user->id);
        $partnerAccess = $input->guard === 'partner'
            ? $this->users->getPartnerAccessContext($user->id)
            : null;

        if ($input->guard === 'partner' && !$partnerAccess) {
            throw new ApiException(403, 'AUTH_PARTNER_STORE_NOT_FOUND', 'Nao foi possivel localizar uma loja vinculada para este acesso.');
        }

        $permissionSlugs = array_values(array_unique(array_filter(array_merge(
            array_column($permissions, 'slug'),
            $partnerAccess['permissions'] ?? []
        ))));
        $accessTtl = (int) ($this->config['tokens']['access_ttl_seconds'] ?? 900);
        $refreshTtl = (int) ($this->config['tokens']['refresh_ttl_seconds'] ?? 2592000);
        $refreshBytes = (int) ($this->config['tokens']['refresh_token_bytes'] ?? 32);
        $now = $this->clock->now();

        $accessToken = $this->tokenIssuer->issueAccessToken([
            'sub' => $user->id,
            'name' => $user->fullName,
            'email' => $user->email,
            'guard' => $input->guard,
            'roles' => array_column($guardRoles, 'slug'),
            'permissions' => $permissionSlugs,
            'partner_access' => $partnerAccess,
            'iat' => $now->getTimestamp(),
            'exp' => $now->modify(sprintf('+%d seconds', $accessTtl))->getTimestamp(),
            'jti' => $this->uuidGenerator->uuid(),
        ]);

        $refreshToken = bin2hex(random_bytes($refreshBytes));
        $this->refreshSessions->create(
            $this->uuidGenerator->uuid(),
            $user->id,
            hash('sha256', $refreshToken),
            $now->modify(sprintf('+%d seconds', $refreshTtl)),
            $context['device_name'] ?? null,
            $context['ip_address'] ?? null,
            $context['user_agent'] ?? null
        );

        return new AuthPayload(
            $accessToken,
            $refreshToken,
            'Bearer',
            $accessTtl,
            [
                'id' => $user->id,
                'name' => $user->fullName,
                'email' => $user->email,
                'guard' => $input->guard,
                'roles' => array_column($guardRoles, 'slug'),
                'permissions' => $permissionSlugs,
                'partner_access' => $partnerAccess,
            ]
        );
    }
}
