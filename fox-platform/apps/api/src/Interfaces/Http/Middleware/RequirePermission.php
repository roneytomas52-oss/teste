<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Middleware;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Http\Response;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class RequirePermission
{
    public function handle(Request $request, callable $next, array $params = []): Response
    {
        if ($params === []) {
            return $next($request);
        }

        $permissions = $request->attribute('auth', [])['permissions'] ?? [];
        $permissionSlugs = array_values(array_unique(array_map(
            static fn (mixed $permission): string => is_array($permission)
                ? (string) ($permission['slug'] ?? '')
                : (string) $permission,
            $permissions
        )));

        foreach ($params as $requiredPermission) {
            if (in_array($requiredPermission, $permissionSlugs, true)) {
                return $next($request);
            }
        }

        throw new ApiException(403, 'AUTH_PERMISSION_FORBIDDEN', 'O usuario autenticado nao possui permissao para esta rota.');
    }
}
