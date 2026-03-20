<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Identity\User;
use FoxPlatform\Api\Domain\Identity\UserRepository;

class PdoUserRepository implements UserRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function findByEmail(string $email): ?User
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM users WHERE LOWER(email) = LOWER(:email) AND deleted_at IS NULL LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $row = $statement->fetch();

        return $row ? User::fromArray($row) : null;
    }

    public function findById(string $id): ?User
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM users WHERE id = :id AND deleted_at IS NULL LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return $row ? User::fromArray($row) : null;
    }

    public function getRolesForUser(string $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT r.id, r.slug, r.scope, r.name
             FROM user_roles ur
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE ur.user_id = :user_id
             ORDER BY r.scope, r.slug'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll() ?: [];
    }

    public function getPermissionsForUser(string $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT DISTINCT p.id, p.slug, p.module, p.action, p.name
             FROM user_roles ur
             INNER JOIN role_permissions rp ON rp.role_id = ur.role_id
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE ur.user_id = :user_id
             ORDER BY p.module, p.action'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll() ?: [];
    }

    public function updatePassword(string $userId, string $passwordHash): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id'
        );
        $statement->execute([
            'id' => $userId,
            'password_hash' => $passwordHash,
        ]);
    }
}
