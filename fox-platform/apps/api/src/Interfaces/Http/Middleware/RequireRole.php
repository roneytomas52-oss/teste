<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Middleware;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Http\Response;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class RequireRole
{
    public function handle(Request $request, callable $next, array $params = []): Response
    {
        $auth = $request->attribute('auth');
        $roles = array_column($auth['roles'] ?? [], 'slug');

        if ($params === []) {
            return $next($request);
        }

        foreach ($params as $requiredRole) {
            if (in_array($requiredRole, $roles, true)) {
                return $next($request);
            }
        }

        throw new ApiException(403, 'AUTH_ROLE_FORBIDDEN', 'O usuario autenticado nao possui permissao para esta rota.');
    }
}
