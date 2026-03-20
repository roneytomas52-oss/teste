<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Partner\PartnerPortalRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoPartnerPortalRepository implements PartnerPortalRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function getProfile(string $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.id, u.full_name, u.email, u.phone, u.status, u.locale, u.last_login_at,
                    COALESCE(ap.legal_name, s.legal_name) AS legal_name,
                    COALESCE(ap.document_number, s.document_number) AS document_number
             FROM users u
             LEFT JOIN partner_accounts ap ON ap.owner_user_id = u.id
             LEFT JOIN stores s ON s.partner_account_id = ap.id
             WHERE u.id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
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
                'INSERT INTO store_hours (id, store_id, weekday, opens_at, closes_at, is_active)
                 VALUES (gen_random_uuid(), :store_id, :weekday, :opens_at, :closes_at, :is_active)'
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

        $statement = $this->pdo->prepare(
            'INSERT INTO store_documents (
                id, store_id, document_type, label, file_name, storage_path, status, metadata
             ) VALUES (
                gen_random_uuid(), :store_id, :document_type, :label, :file_name, :storage_path, :status, :metadata::jsonb
             )
             RETURNING id, document_type, label, file_name, storage_path, status, metadata, created_at'
        );

        $statement->execute([
            'store_id' => $storeId,
            'document_type' => $document['document_type'],
            'label' => $document['label'],
            'file_name' => $document['file_name'],
            'storage_path' => $document['storage_path'],
            'status' => $document['status'] ?? 'pending',
            'metadata' => json_encode($document['metadata'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $created = $statement->fetch();

        return [
            'document' => [
                'id' => $created['id'],
                'document_type' => $created['document_type'],
                'label' => $created['label'],
                'file_name' => $created['file_name'],
                'storage_path' => $created['storage_path'],
                'status' => $created['status'],
                'metadata' => json_decode((string) $created['metadata'], true) ?: [],
                'created_at' => $created['created_at'],
            ],
            'documents' => $this->loadStoreDocuments($storeId),
        ];
    }

    private function resolveStoreIdentity(string $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT ap.id AS partner_account_id, s.id AS store_id
             FROM partner_accounts ap
             INNER JOIN stores s ON s.partner_account_id = ap.id
             WHERE ap.owner_user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $identity = $statement->fetch();

        if (!$identity) {
            throw new ApiException(404, 'PARTNER_STORE_NOT_FOUND', 'Nao foi possivel localizar a loja do parceiro.');
        }

        return [$identity['partner_account_id'], $identity['store_id']];
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
            'SELECT weekday, opens_at::text AS opens_at, closes_at::text AS closes_at, is_active
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
}
