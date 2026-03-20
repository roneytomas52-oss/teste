<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class HealthController
{
    public function __construct(
        private readonly array $appConfig
    ) {
    }

    public function show(Request $request)
    {
        return ApiResponse::success([
            'service' => $this->appConfig['name'] ?? 'fox-platform-api',
            'version' => $this->appConfig['version'] ?? '0.1.0',
            'status' => 'ok',
            'timestamp' => date(DATE_ATOM),
            'environment' => $_ENV['APP_ENV'] ?? 'local',
        ]);
    }
}
