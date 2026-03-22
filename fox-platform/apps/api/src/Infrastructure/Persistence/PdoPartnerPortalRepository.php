<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Partner\PartnerPortalRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoPartnerPortalRepository implements PartnerPortalRepository
{
    use SupportsSqlDialect;

    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function getProfile(string $userId): array
    {
        [$partnerAccountId, $storeId, $accessContext] = $this->resolveStoreIdentity($userId);

        $statement = $this->pdo->prepare(
            'SELECT u.id, u.full_name, u.email, u.phone, u.status, u.locale, u.last_login_at,
                    ap.legal_name,
                    ap.document_number
             FROM users u
             INNER JOIN partner_accounts ap ON ap.id = :partner_account_id
             INNER JOIN stores s ON s.id = :store_id
             WHERE u.id = :user_id
             LIMIT 1'
        );
        $statement->execute([
            'user_id' => $userId,
            'partner_account_id' => $partnerAccountId,
            'store_id' => $storeId,
        ]);
        $profile = $statement->fetch();

        if (!$profile) {
            throw new ApiException(404, 'PARTNER_PROFILE_NOT_FOUND', 'Perfil do parceiro nao encontrado.');
        }

        $roles = $this->loadRoles($userId);

        return [
            'id' => $profile['id'],
            'full_name' => $profile['full_name'],
            'email' => $profile['email'],
            'phone' => $profile['phone'],
            'status' => $profile['status'],
            'locale' => $profile['locale'],
            'last_login_at' => $profile['last_login_at'],
            'legal_name' => $profile['legal_name'],
            'document_number' => $profile['document_number'],
            'roles' => $roles,
            'access_type' => $accessContext['access_type'],
            'team_role' => $accessContext['team_role'],
            'store_id' => $storeId,
        ];
    }

    public function updateProfile(string $userId, array $data): array
    {
        $statement = $this->pdo->prepare(
            'UPDATE users
             SET full_name = :full_name,
                 email = :email,
                 phone = :phone,
                 updated_at = NOW()
             WHERE id = :user_id'
        );

        $statement->execute([
            'user_id' => $userId,
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        return $this->getProfile($userId);
    }

    public function getStore(string $userId): array
    {
        [$partnerAccountId, $storeId] = $this->resolveStoreIdentity($userId);

        $statement = $this->pdo->prepare(
            'SELECT s.*, ap.legal_name AS partner_legal_name, ap.status AS partner_status
             FROM stores s
             INNER JOIN partner_accounts ap ON ap.id = s.partner_account_id
             WHERE s.id = :store_id AND ap.id = :partner_account_id
             LIMIT 1'
        );
        $statement->execute([
            'store_id' => $storeId,
            'partner_account_id' => $partnerAccountId,
        ]);
        $store = $statement->fetch();

        if (!$store) {
            throw new ApiException(404, 'PARTNER_STORE_NOT_FOUND', 'Loja do parceiro nao encontrada.');
        }

        return [
            'store' => [
                'id' => $store['id'],
                'trade_name' => $store['trade_name'],
                'legal_name' => $store['legal_name'],
                'document_number' => $store['document_number'],
                'email' => $store['email'],
                'phone' => $store['phone'],
                'status' => $store['status'],
                'city' => $store['city'],
                'state' => $store['state'],
                'country' => $store['country'],
                'description' => $store['description'] ?? null,
            ],
            'hours' => $this->loadStoreHours($storeId),
            'documents' => $this->loadStoreDocuments($storeId),
        ];
    }

    public function updateStore(string $userId, array $data): array
    {
        [, $storeId] = $this->resolveStoreIdentity($userId);

        $statement = $this->pdo->prepare(
            'UPDATE stores
             SET trade_name = :trade_name,
                 legal_name = :legal_name,
                 document_number = :document_number,
                 email = :email,
                 phone = :phone,
                 city = :city,
                 state = :state,
                 country = :country,
                 description = :description,
                 updated_at = NOW()
             WHERE id = :store_id'
        );

        $statement->execute([
            'store_id' => $storeId,
            'trade_name' => $data['trade_name'],
            'legal_name' => $data['legal_name'],
            'document_number' => $data['document_number'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'description' => $data['description'],
        ]);

        return $this->getStore($userId);
    }

    public function replaceStoreHours(string $userId, array $hours): array
    {
        [, $storeId] = $this->resolveStoreIdentity($userId);

        $this->pdo->beginTransaction();
        try {
            $delete = $this->pdo->prepare('DELETE FROM store_hours WHERE store_id = :store_id');
            $delete->execute(['store_id' => $storeId]);

            $insert = $this->pdo->prepare(
                sprintf(
                    'INSERT INTO store_hours (id, store_id, weekday, opens_at, closes_at, is_active)
                     VALUES (%s, :store_id, :weekday, :opens_at, :closes_at, :is_active)',
                    $this->uuidExpression()
                )
            );

            foreach ($hours as $hour) {
                $insert->execute([
                    'store_id' => $storeId,
                    'weekday' => $hour['weekday'],
                    'opens_at' => $hour['opens_at'],
                    'closes_at' => $hour['closes_at'],
                    'is_active' => $hour['is_active'],
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return [
            'store_id' => $storeId,
            'hours' => $this->loadStoreHours($storeId),
        ];
    }

    public function addStoreDocument(string $userId, array $document): array
    {
        [, $storeId] = $this->resolveStoreIdentity($userId);
        $documentId = $this->newUuid();

        $statement = $this->pdo->prepare(
            sprintf(
            'INSERT INTO store_documents (
                id, store_id, document_type, label, file_name, storage_path, status, metadata
             ) VALUES (
                :id, :store_id, :document_type, :label, :file_name, :storage_path, :status, %s
             )',
             $this->jsonPlaceholder(':metadata')
            )
        );

        $statement->execute([
            'id' => $documentId,
            'store_id' => $storeId,
            'document_type' => $document['document_type'],
            'label' => $document['label'],
            'file_name' => $document['file_name'],
            'storage_path' => $document['storage_path'],
            'status' => $document['status'] ?? 'pending',
            'metadata' => json_encode($document['metadata'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $documents = $this->loadStoreDocuments($storeId);
        $created = null;
        foreach ($documents as $item) {
            if ($item['id'] === $documentId) {
                $created = $item;
                break;
            }
        }

        return [
            'document' => $created,
            'documents' => $documents,
        ];
    }

    public function getTeam(string $userId): array
    {
        [, $storeId] = $this->resolveStoreIdentity($userId);
        $members = $this->loadTeamMembers($storeId);

        return [
            'summary' => [
                ['label' => 'membros ativos', 'value' => (string) count(array_filter($members, static fn (array $member) => $member['status_key'] === 'active'))],
                ['label' => 'convites pendentes', 'value' => (string) count(array_filter($members, static fn (array $member) => $member['status_key'] === 'invited'))],
                ['label' => 'perfis configurados', 'value' => (string) count(array_unique(array_map(static fn (array $member) => $member['role_label'], $members)))],
            ],
            'members' => $members,
        ];
    }

    public function createTeamMember(string $userId, array $data): array
    {
        [, $storeId] = $this->resolveStoreIdentity($userId);

        $this->pdo->beginTransaction();

        try {
            $teamUserId = $this->ensureTeamMemberUser($storeId, $userId, [
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role_slug' => $data['role_slug'],
                'status' => 'invited',
            ]);

            $statement = $this->pdo->prepare(
            sprintf(
            "INSERT INTO store_team_members (
                id, store_id, user_id, invited_by_user_id, invite_token, invite_expires_at, full_name, email, phone, role_slug, status, permissions
             ) VALUES (
                %s, :store_id, :user_id, :invited_by_user_id, :invite_token, %s, :full_name, :email, :phone, :role_slug, 'invited', %s
             )",
             $this->uuidExpression(),
             $this->nowPlusDays(7),
             $this->jsonPlaceholder(':permissions')
            )
            );
            $statement->execute([
                'store_id' => $storeId,
                'user_id' => $teamUserId,
                'invited_by_user_id' => $userId,
                'invite_token' => bin2hex(random_bytes(24)),
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role_slug' => $data['role_slug'],
                'permissions' => json_encode($data['permissions'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getTeam($userId);
    }

    public function updateTeamMember(string $userId, string $memberId, array $data): array
    {
        [, $storeId] = $this->resolveStoreIdentity($userId);
        $member = $this->findTeamMember($storeId, $memberId);

        if (!$member) {
            throw new ApiException(404, 'TEAM_MEMBER_NOT_FOUND', 'Membro da equipe nao encontrado para esta loja.');
        }

        $this->pdo->beginTransaction();

        try {
            $teamUserId = $this->ensureTeamMemberUser($storeId, $userId, [
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role_slug' => $data['role_slug'],
                'status' => $member['status'],
                'user_id' => $member['user_id'] ?? null,
            ]);

            $statement = $this->pdo->prepare(
            sprintf(
            "UPDATE store_team_members
             SET user_id = :user_id,
                 full_name = :full_name,
                 email = :email,
                 phone = :phone,
                 role_slug = :role_slug,
                 permissions = %s,
                 updated_at = NOW()
             WHERE id = :member_id
               AND store_id = :store_id",
               $this->jsonPlaceholder(':permissions')
            )
            );
            $statement->execute([
                'user_id' => $teamUserId,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'role_slug' => $data['role_slug'],
                'permissions' => json_encode($data['permissions'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'member_id' => $memberId,
                'store_id' => $storeId,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getTeam($userId);
    }

    public function updateTeamMemberStatus(string $userId, string $memberId, array $data): array
    {
        [, $storeId] = $this->resolveStoreIdentity($userId);
        $member = $this->findTeamMember($storeId, $memberId);

        if (!$member) {
            throw new ApiException(404, 'TEAM_MEMBER_NOT_FOUND', 'Membro da equipe nao encontrado para esta loja.');
        }

        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
            "UPDATE store_team_members
             SET status = :status,
                 activated_at = CASE WHEN :status = 'active' AND activated_at IS NULL THEN NOW() ELSE activated_at END,
                 updated_at = NOW()
             WHERE id = :member_id
               AND store_id = :store_id"
            );
            $statement->execute([
                'status' => $data['status'],
                'member_id' => $memberId,
                'store_id' => $storeId,
            ]);

            $userStatus = match ($data['status']) {
                'active' => 'active',
                'suspended' => 'suspended',
                default => 'pending',
            };

            if (!empty($member['user_id'])) {
                $syncUser = $this->pdo->prepare(
                    'UPDATE users SET status = :status, updated_at = NOW() WHERE id = :user_id'
                );
                $syncUser->execute([
                    'status' => $userStatus,
                    'user_id' => $member['user_id'],
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getTeam($userId);
    }

    private function resolveStoreIdentity(string $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM (
                SELECT
                    ap.id AS partner_account_id,
                    s.id AS store_id,
                    \'owner\' AS access_type,
                    \'partner_owner\' AS team_role
                FROM partner_accounts ap
                INNER JOIN stores s ON s.partner_account_id = ap.id
                WHERE ap.owner_user_id = :owner_user_id

                UNION ALL

                SELECT
                    s.partner_account_id,
                    stm.store_id,
                    \'team_member\' AS access_type,
                    CASE
                        WHEN stm.role_slug = \'manager\' THEN \'partner_manager\'
                        ELSE \'partner_staff\'
                    END AS team_role
                FROM store_team_members stm
                INNER JOIN stores s ON s.id = stm.store_id
                WHERE stm.user_id = :team_user_id
                  AND stm.status = \'active\'
             ) partner_access
             LIMIT 1'
        );
        $statement->execute([
            'owner_user_id' => $userId,
            'team_user_id' => $userId,
        ]);
        $identity = $statement->fetch();

        if (!$identity) {
            throw new ApiException(404, 'PARTNER_STORE_NOT_FOUND', 'Nao foi possivel localizar a loja do parceiro.');
        }

        return [
            $identity['partner_account_id'],
            $identity['store_id'],
            [
                'access_type' => $identity['access_type'],
                'team_role' => $identity['team_role'],
            ],
        ];
    }

    private function loadRoles(string $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT r.slug, r.scope, r.name
             FROM user_roles ur
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE ur.user_id = :user_id
             ORDER BY r.scope, r.slug'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll() ?: [];
    }

    private function loadStoreHours(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            $this->isMySql()
                ? 'SELECT weekday, opens_at, closes_at, is_active
                   FROM store_hours
                   WHERE store_id = :store_id
                   ORDER BY weekday'
                : 'SELECT weekday, opens_at::text AS opens_at, closes_at::text AS closes_at, is_active
                   FROM store_hours
                   WHERE store_id = :store_id
                   ORDER BY weekday'
        );
        $statement->execute(['store_id' => $storeId]);

        return array_map(
            static fn (array $row) => [
                'weekday' => (int) $row['weekday'],
                'opens_at' => substr((string) $row['opens_at'], 0, 5),
                'closes_at' => substr((string) $row['closes_at'], 0, 5),
                'is_active' => filter_var($row['is_active'], FILTER_VALIDATE_BOOL),
            ],
            $statement->fetchAll() ?: []
        );
    }

    private function loadStoreDocuments(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, document_type, label, file_name, storage_path, status, metadata, created_at
             FROM store_documents
             WHERE store_id = :store_id
             ORDER BY created_at DESC'
        );
        $statement->execute(['store_id' => $storeId]);

        return array_map(
            static fn (array $row) => [
                'id' => $row['id'],
                'document_type' => $row['document_type'],
                'label' => $row['label'],
                'file_name' => $row['file_name'],
                'storage_path' => $row['storage_path'],
                'status' => $row['status'],
                'metadata' => json_decode((string) $row['metadata'], true) ?: [],
                'created_at' => $row['created_at'],
            ],
            $statement->fetchAll() ?: []
        );
    }

    private function loadTeamMembers(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, user_id, full_name, email, phone, role_slug, status, permissions, last_login_at, created_at
             FROM store_team_members
             WHERE store_id = :store_id
             ORDER BY created_at DESC, full_name ASC"
        );
        $statement->execute(['store_id' => $storeId]);

        return array_map(function (array $row): array {
            $permissions = json_decode((string) $row['permissions'], true) ?: [];

            return [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'role_slug' => $row['role_slug'],
                'role_label' => $this->normalizeTeamRoleLabel((string) $row['role_slug']),
                'status' => $this->normalizeTeamStatusLabel((string) $row['status']),
                'status_key' => $row['status'],
                'status_type' => $this->normalizeTeamStatusType((string) $row['status']),
                'permissions' => $permissions,
                'last_login_at' => $row['last_login_at'] ? $this->formatDateTime((string) $row['last_login_at']) : '-',
                'created_at' => $this->formatDateTime((string) $row['created_at']),
            ];
        }, $statement->fetchAll() ?: []);
    }

    private function findTeamMember(string $storeId, string $memberId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT id, user_id, full_name, email, phone, role_slug, status, permissions
             FROM store_team_members
             WHERE store_id = :store_id
               AND id = :member_id
             LIMIT 1"
        );
        $statement->execute([
            'store_id' => $storeId,
            'member_id' => $memberId,
        ]);

        return $statement->fetch();
    }

    private function ensureTeamMemberUser(string $storeId, string $actorUserId, array $data): string
    {
        $existingUserId = trim((string) ($data['user_id'] ?? ''));
        $email = trim((string) $data['email']);

        if ($existingUserId === '') {
            $lookup = $this->pdo->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(:email) AND deleted_at IS NULL LIMIT 1');
            $lookup->execute(['email' => $email]);
            $existingUserId = (string) ($lookup->fetchColumn() ?: '');
        }

        if ($existingUserId === '') {
            $createUser = $this->pdo->prepare(
                'INSERT INTO users (
                    id, full_name, email, phone, password_hash, status, locale
                 ) VALUES (
                    :id, :full_name, :email, :phone, :password_hash, :status, :locale
                 )'
            );
            $existingUserId = $this->newUuid();
            $createUser->execute([
                'id' => $existingUserId,
                'full_name' => $data['full_name'],
                'email' => $email,
                'phone' => $data['phone'] !== '' ? $data['phone'] : null,
                'password_hash' => password_hash('password', PASSWORD_BCRYPT),
                'status' => $data['status'] === 'active' ? 'active' : 'pending',
                'locale' => 'pt_BR',
            ]);
        } else {
            $updateUser = $this->pdo->prepare(
                'UPDATE users
                 SET full_name = :full_name,
                     email = :email,
                     phone = :phone,
                     status = CASE WHEN :status = \'active\' THEN \'active\' ELSE status END,
                     updated_at = NOW()
                 WHERE id = :user_id'
            );
            $updateUser->execute([
                'full_name' => $data['full_name'],
                'email' => $email,
                'phone' => $data['phone'] !== '' ? $data['phone'] : null,
                'status' => $data['status'],
                'user_id' => $existingUserId,
            ]);
        }

        $this->syncTeamMemberRole($existingUserId, (string) $data['role_slug']);

        return $existingUserId;
    }

    private function syncTeamMemberRole(string $userId, string $teamRole): void
    {
        $roleSlug = $teamRole === 'manager' ? 'partner_manager' : 'partner_staff';

        $deleteRoles = $this->pdo->prepare(
            "DELETE FROM user_roles
             WHERE user_id = :user_id
               AND role_id IN (
                   SELECT id FROM roles WHERE slug IN ('partner_manager', 'partner_staff')
               )"
        );
        $deleteRoles->execute(['user_id' => $userId]);

        $insertRole = $this->pdo->prepare(
            sprintf(
                "INSERT INTO user_roles (id, user_id, role_id, scope_type, scope_id)
                 SELECT %s, :user_id, r.id, 'portal', NULL
                 FROM roles r
                 WHERE r.slug = :role_slug",
                $this->uuidExpression()
            )
        );
        $insertRole->execute([
            'user_id' => $userId,
            'role_slug' => $roleSlug,
        ]);
    }

    private function normalizeTeamRoleLabel(string $role): string
    {
        return match ($role) {
            'manager' => 'Gerente da loja',
            'catalog' => 'Catalogo e estoque',
            'operations' => 'Operacao',
            'finance' => 'Financeiro',
            'support' => 'Suporte',
            default => 'Equipe',
        };
    }

    private function normalizeTeamStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'ativo',
            'suspended' => 'suspenso',
            default => 'convite pendente',
        };
    }

    private function normalizeTeamStatusType(string $status): string
    {
        return match ($status) {
            'active' => 'success',
            'suspended' => 'danger',
            default => 'warning',
        };
    }

    private function formatDateTime(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        return date('d/m/Y H:i', strtotime($value));
    }
}
