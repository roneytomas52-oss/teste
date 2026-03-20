<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Middleware;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Http\Response;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class JsonOnly
{
    public function handle(Request $request, callable $next, array $params = []): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $contentType = strtolower((string) $request->header('content-type', ''));
            if ($request->rawBody() !== '' && !str_contains($contentType, 'application/json')) {
                throw new ApiException(415, 'UNSUPPORTED_MEDIA_TYPE', 'As rotas da API aceitam apenas application/json.');
            }
        }

        return $next($request);
    }
}
