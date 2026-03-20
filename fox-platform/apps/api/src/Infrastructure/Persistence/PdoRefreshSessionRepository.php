<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use DateTimeImmutable;
use PDO;
use FoxPlatform\Api\Domain\Identity\RefreshSession;
use FoxPlatform\Api\Domain\Identity\RefreshSessionRepository;

class PdoRefreshSessionRepository implements RefreshSessionRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function create(
        string $id,
        string $userId,
        string $refreshTokenHash,
        DateTimeImmutable $expiresAt,
        ?string $deviceName,
        ?string $ipAddress,
        ?string $userAgent
    ): void {
        $statement = $this->pdo->prepare(
            'INSERT INTO refresh_sessions (
                id, user_id, refresh_token_hash, device_name, ip_address, user_agent, expires_at
            ) VALUES (
                :id, :user_id, :refresh_token_hash, :device_name, :ip_address, :user_agent, :expires_at
            )'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'refresh_token_hash' => $refreshTokenHash,
            'device_name' => $deviceName,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'expires_at' => $expiresAt->format(DATE_ATOM),
        ]);
    }

    public function findActiveByTokenHash(string $refreshTokenHash): ?RefreshSession
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM refresh_sessions
             WHERE refresh_token_hash = :refresh_token_hash
               AND revoked_at IS NULL
               AND expires_at > NOW()
             LIMIT 1'
        );
        $statement->execute(['refresh_token_hash' => $refreshTokenHash]);
        $row = $statement->fetch();

        return $row ? RefreshSession::fromArray($row) : null;
    }

    public function revokeById(string $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE refresh_sessions SET revoked_at = NOW() WHERE id = :id AND revoked_at IS NULL'
        );
        $statement->execute(['id' => $id]);
    }

    public function revokeByTokenHash(string $refreshTokenHash): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE refresh_sessions SET revoked_at = NOW()
             WHERE refresh_token_hash = :refresh_token_hash AND revoked_at IS NULL'
        );
        $statement->execute(['refresh_token_hash' => $refreshTokenHash]);
    }

    public function revokeAllByUserId(string $userId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE refresh_sessions SET revoked_at = NOW()
             WHERE user_id = :user_id AND revoked_at IS NULL'
        );
        $statement->execute(['user_id' => $userId]);
    }
}
