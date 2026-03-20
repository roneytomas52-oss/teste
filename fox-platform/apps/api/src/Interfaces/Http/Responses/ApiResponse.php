<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Responses;

use FoxPlatform\Api\Infrastructure\Http\Response;

class ApiResponse
{
    public static function success(
        array $data = [],
        ?string $message = null,
        array $meta = [],
        int $status = 200
    ): Response {
        return Response::json([
            'success' => true,
            'data' => $data,
            'meta' => $meta,
            'message' => $message,
        ], $status);
    }
}
