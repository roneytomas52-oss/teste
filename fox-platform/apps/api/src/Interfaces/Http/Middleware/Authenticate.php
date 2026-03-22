<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Middleware;

use FoxPlatform\Api\Domain\Identity\TokenIssuer;
use FoxPlatform\Api\Domain\Identity\UserRepository;
use FoxPlatform\Api\Infrastructure\Auth\BearerTokenParser;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Http\Response;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class Authenticate
{
    public function __construct(
        private readonly BearerTokenParser $tokenParser,
        private readonly TokenIssuer $tokenIssuer,
        private readonly UserRepository $users
    ) {
    }

    public function handle(Request $request, callable $next, array $params = []): Response
    {
        $token = $this->tokenParser->extract($request);
        if (!$token) {
            throw new ApiException(401, 'AUTH_TOKEN_MISSING', 'Token de acesso nao informado.');
        }

        $payload = $this->tokenIssuer->verifyAccessToken($token);
        if (!$payload) {
            throw new ApiException(401, 'AUTH_TOKEN_INVALID', 'Token de acesso invalido ou expirado.');
        }

        $user = $this->users->findById((string) $payload['sub']);
        if (!$user || !$user->isActive()) {
            throw new ApiException(401, 'AUTH_USER_UNAVAILABLE', 'Usuario autenticado nao esta disponivel.');
        }

        $roles = $this->users->getRolesForUser($user->id);
        $permissions = $this->users->getPermissionsForUser($user->id);
        $partnerAccess = ((string) ($payload['guard'] ?? '') === 'partner')
            ? $this->users->getPartnerAccessContext($user->id)
            : null;
        $permissionSlugs = array_values(array_unique(array_filter(array_merge(
            array_column($permissions, 'slug'),
            $partnerAccess['permissions'] ?? []
        ))));

        return $next($request->withAttribute('auth', [
            'user_id' => $user->id,
            'guard' => (string) ($payload['guard'] ?? ''),
            'roles' => $roles,
            'permissions' => $permissionSlugs,
            'permission_records' => $permissions,
            'partner_access' => $partnerAccess,
            'claims' => $payload,
        ]));
    }
}
