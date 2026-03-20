<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Controllers;

use FoxPlatform\Api\Application\Auth\GetAuthenticatedUser;
use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Interfaces\Http\Responses\ApiResponse;

class MeController
{
    public function __construct(
        private readonly GetAuthenticatedUser $getAuthenticatedUser
    ) {
    }

    public function show(Request $request)
    {
        $auth = $request->attribute('auth', []);
        $view = ($this->getAuthenticatedUser)($auth);

        return ApiResponse::success($view->toArray());
    }
}
