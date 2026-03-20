<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Identity;

use DateTimeImmutable;

class RefreshSession
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $refreshTokenHash,
        public readonly ?string $deviceName,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly DateTimeImmutable $expiresAt,
        public readonly ?DateTimeImmutable $revokedAt
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['user_id'],
            (string) $data['refresh_token_hash'],
            $data['device_name'] ?? null,
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null,
            new DateTimeImmutable((string) $data['expires_at']),
            isset($data['revoked_at']) ? new DateTimeImmutable((string) $data['revoked_at']) : null
        );
    }
}
