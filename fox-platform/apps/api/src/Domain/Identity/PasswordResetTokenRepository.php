<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Identity;

use DateTimeImmutable;

interface PasswordResetTokenRepository
{
    public function create(string $email, string $tokenHash, DateTimeImmutable $expiresAt): void;

    public function existsValid(string $email, string $tokenHash): bool;

    public function deleteByEmail(string $email): void;
}
