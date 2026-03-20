<?php

declare(strict_types=1);

use FoxPlatform\Api\Infrastructure\Http\Router;
use FoxPlatform\Api\Infrastructure\Support\Container;

return static function (Container $container, Router $router, string $apiRoot): void {
    $register = require $apiRoot . '/routes/api.php';
    $register($router, $container);
};
