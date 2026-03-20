<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Auth;

use FoxPlatform\Api\Domain\Identity\PasswordHasher;

class BcryptPasswordHasher implements PasswordHasher
{
    public function hash(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}
