<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\AccessControl;

class Role
{
    public function __construct(
        public readonly string $id,
        public readonly string $slug,
        public readonly string $scope,
        public readonly string $name
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['slug'],
            (string) $data['scope'],
            (string) $data['name']
        );
    }
}
