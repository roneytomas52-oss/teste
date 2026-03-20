<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Auth\DTO;

class LoginInput
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $guard
    ) {
    }
}
