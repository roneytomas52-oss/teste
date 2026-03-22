<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Identity\User;
use FoxPlatform\Api\Domain\Identity\UserRepository;

class PdoUserRepository implements UserRepository
{
    use SupportsSqlDialect;

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

    public function getPartnerAccessContext(string $userId): ?array
    {
        $statement = $this->pdo->prepare(
            sprintf(
            "SELECT *
             FROM (
                SELECT
                    s.id AS store_id,
                    s.trade_name,
                    s.status AS store_status,
                    s.partner_account_id,
                    'owner' AS access_type,
                    'partner_owner' AS access_role,
                    %s AS permissions,
                    ap.owner_user_id AS reference_user_id
                FROM partner_accounts ap
                INNER JOIN stores s ON s.partner_account_id = ap.id
                WHERE ap.owner_user_id = :owner_user_id

                UNION ALL

                SELECT
                    s.id AS store_id,
                    s.trade_name,
                    s.status AS store_status,
                    s.partner_account_id,
                    'team_member' AS access_type,
                    CASE
                        WHEN stm.role_slug = 'manager' THEN 'partner_manager'
                        ELSE 'partner_staff'
                    END AS access_role,
                    stm.permissions,
                    stm.user_id AS reference_user_id
                FROM store_team_members stm
                INNER JOIN stores s ON s.id = stm.store_id
                WHERE stm.user_id = :team_user_id
                  AND stm.status = 'active'
             ) partner_access
             LIMIT 1",
             $this->isMySql() ? "'[]'" : "'[]'::jsonb"
            )
        );
        $statement->execute([
            'owner_user_id' => $userId,
            'team_user_id' => $userId,
        ]);
        $row = $statement->fetch();

        if (!$row) {
            return null;
        }

        return [
            'store_id' => $row['store_id'],
            'trade_name' => $row['trade_name'],
            'store_status' => $row['store_status'],
            'partner_account_id' => $row['partner_account_id'],
            'access_type' => $row['access_type'],
            'access_role' => $row['access_role'],
            'permissions' => json_decode((string) ($row['permissions'] ?? '[]'), true) ?: [],
            'reference_user_id' => $row['reference_user_id'],
        ];
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
