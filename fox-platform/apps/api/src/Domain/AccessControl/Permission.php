<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\AccessControl;

class Permission
{
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $module,
        public readonly string $action,
        public readonly string $name
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['slug'],
            (string) $data['module'],
            (string) $data['action'],
            (string) $data['name']
        );
    }
}
