<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Middleware;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Http\Response;

class CorsMiddleware
{
    public function __construct(
        private readonly array $config
    ) {
    }

    public function handle(Request $request, callable $next, array $params = []): Response
    {
        $response = $next($request);
        $origin = (string) $request->header('origin', '');
        $allowedOrigins = $this->config['allowed_origins'] ?? [];

        if ($origin !== '' && (in_array('*', $allowedOrigins, true) || in_array($origin, $allowedOrigins, true))) {
            $response = $response
                ->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Vary', 'Origin')
                ->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $this->config['allowed_methods'] ?? []))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $this->config['allowed_headers'] ?? []))
            ->withHeader('Access-Control-Expose-Headers', implode(', ', $this->config['exposed_headers'] ?? []))
            ->withHeader('Access-Control-Max-Age', (string) ($this->config['max_age'] ?? 86400));
    }
}
