<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Identity;

interface PasswordHasher
{
    public function hash(string $plainPassword): string;

    public function verify(string $plainPassword, string $hashedPassword): bool;
}
