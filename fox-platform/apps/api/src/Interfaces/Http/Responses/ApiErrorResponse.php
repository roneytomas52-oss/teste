<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Responses;

use FoxPlatform\Api\Infrastructure\Http\Response;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class ApiErrorResponse
{
    public static function fromException(ApiException $exception): Response
    {
        return Response::json([
            'success' => false,
            'error' => [
                'code' => $exception->codeName(),
                'message' => $exception->getMessage(),
                'details' => $exception->details(),
            ],
        ], $exception->status());
    }
}
