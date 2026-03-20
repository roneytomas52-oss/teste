<?php

declare(strict_types=1);

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Http\Response;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

$apiRoot = dirname(__DIR__);

require_once $apiRoot . '/bootstrap/autoload.php';
require_once $apiRoot . '/bootstrap/env.php';

fox_api_load_env($apiRoot);

$containerFactory = require $apiRoot . '/bootstrap/container.php';
$container = $containerFactory($apiRoot);

$router = $container->get('router');
$routesBootstrap = require $apiRoot . '/bootstrap/routes.php';
$routesBootstrap($container, $router, $apiRoot);

return static function () use ($container, $router): Response {
    $request = Request::fromGlobals();
    $cors = $container->get('middleware.cors');

    try {
        if ($request->method() === 'OPTIONS') {
            $response = Response::empty(204);
            return $cors->handle($request, static fn () => $response);
        }

        $response = $router->dispatch($request, $container);
        return $cors->handle($request, static fn () => $response);
    } catch (ApiException $exception) {
        $response = Response::json([
            'success' => false,
            'error' => [
                'code' => $exception->codeName(),
                'message' => $exception->getMessage(),
                'details' => $exception->details(),
            ],
        ], $exception->status());
        return $cors->handle($request, static fn () => $response);
    } catch (Throwable $exception) {
        $isDebug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);

        $response = Response::json([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $isDebug ? $exception->getMessage() : 'Erro interno do servidor.',
            ],
        ], 500);
        return $cors->handle($request, static fn () => $response);
    }
};
