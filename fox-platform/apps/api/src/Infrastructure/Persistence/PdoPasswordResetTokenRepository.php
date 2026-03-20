<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use DateTimeImmutable;
use PDO;
use FoxPlatform\Api\Domain\Identity\PasswordResetTokenRepository;

class PdoPasswordResetTokenRepository implements PasswordResetTokenRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function create(string $email, string $tokenHash, DateTimeImmutable $expiresAt): void
    {
        $this->deleteByEmail($email);

        $statement = $this->pdo->prepare(
            'INSERT INTO password_reset_tokens (email, token_hash, expires_at)
             VALUES (:email, :token_hash, :expires_at)'
        );

        $statement->execute([
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt->format(DATE_ATOM),
        ]);
    }

    public function existsValid(string $email, string $tokenHash): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT 1
             FROM password_reset_tokens
             WHERE email = :email
               AND token_hash = :token_hash
               AND expires_at > NOW()
             LIMIT 1'
        );
        $statement->execute([
            'email' => $email,
            'token_hash' => $tokenHash,
        ]);

        return (bool) $statement->fetchColumn();
    }

    public function deleteByEmail(string $email): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM password_reset_tokens WHERE email = :email'
        );
        $statement->execute(['email' => $email]);
    }
}
