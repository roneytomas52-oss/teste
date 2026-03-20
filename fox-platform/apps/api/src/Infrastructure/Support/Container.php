<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Support;

use Closure;

class Container
{
    private array $bindings = [];

    private array $instances = [];

    public function set(string $id, mixed $value, bool $shared = true): void
    {
        $this->bindings[$id] = [
            'value' => $value,
            'shared' => $shared,
        ];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->bindings) || array_key_exists($id, $this->instances);
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (!array_key_exists($id, $this->bindings)) {
            throw new ApiException(500, 'CONTAINER_BINDING_NOT_FOUND', sprintf('Binding "%s" nao encontrado.', $id));
        }

        $binding = $this->bindings[$id];
        $value = $binding['value'];

        if ($value instanceof Closure) {
            $reflection = new \ReflectionFunction($value);
            $resolved = $reflection->getNumberOfParameters() > 0
                ? $value($this)
                : $value();
            if ($binding['shared']) {
                $this->instances[$id] = $resolved;
            }

            return $resolved;
        }

        if ($binding['shared']) {
            $this->instances[$id] = $value;
        }

        return $value;
    }
}
