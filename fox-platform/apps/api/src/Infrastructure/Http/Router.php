<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Http;

use FoxPlatform\Api\Infrastructure\Support\ApiException;
use FoxPlatform\Api\Infrastructure\Support\Container;

class Router
{
    private array $routes = [];

    public function get(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->add('GET', $pattern, $handler, $middlewares);
    }

    public function post(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->add('POST', $pattern, $handler, $middlewares);
    }

    public function put(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->add('PUT', $pattern, $handler, $middlewares);
    }

    public function delete(string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->add('DELETE', $pattern, $handler, $middlewares);
    }

    public function add(string $method, string $pattern, mixed $handler, array $middlewares = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(Request $request, Container $container): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            $matched = $this->match($route['pattern'], $request->path());
            if ($matched === null) {
                continue;
            }

            foreach ($matched as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }

            $core = function (Request $request) use ($route, $container): Response {
                return $this->invokeHandler($route['handler'], $request, $container);
            };

            $pipeline = array_reduce(
                array_reverse($route['middlewares']),
                function (callable $next, string $middlewareSpec) use ($container): callable {
                    return function (Request $request) use ($middlewareSpec, $container, $next): Response {
                        [$alias, $params] = $this->parseMiddleware($middlewareSpec);
                        $middleware = $container->get('middleware.' . $alias);
                        return $middleware->handle($request, $next, $params);
                    };
                },
                $core
            );

            return $pipeline($request);
        }

        throw new ApiException(404, 'ROUTE_NOT_FOUND', 'Rota nao encontrada.');
    }

    private function invokeHandler(mixed $handler, Request $request, Container $container): Response
    {
        if (is_callable($handler)) {
            return $handler($request, $container);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = $container->has($class) ? $container->get($class) : new $class();
            return $controller->{$method}($request);
        }

        throw new ApiException(500, 'INVALID_ROUTE_HANDLER', 'Handler de rota invalido.');
    }

    private function parseMiddleware(string $middleware): array
    {
        [$alias, $rawParams] = array_pad(explode(':', $middleware, 2), 2, '');
        $params = $rawParams === '' ? [] : array_map('trim', explode(',', $rawParams));
        return [$alias, $params];
    }

    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        $attributes = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }
}
