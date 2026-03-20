<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Identity;

use DateTimeImmutable;

interface RefreshSessionRepository
{
    public function create(
        string $id,
        string $userId,
        string $refreshTokenHash,
        DateTimeImmutable $expiresAt,
        ?string $deviceName,
        ?string $ipAddress,
        ?string $userAgent
    ): void;

    public function findActiveByTokenHash(string $refreshTokenHash): ?RefreshSession;

    public function revokeById(string $id): void;

    public function revokeByTokenHash(string $refreshTokenHash): void;

    public function revokeAllByUserId(string $userId): void;
}
