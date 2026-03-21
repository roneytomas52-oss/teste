<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Partner\PartnerCatalogRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoPartnerCatalogRepository implements PartnerCatalogRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function listCatalog(string $userId): array
    {
        $storeId = $this->resolveStoreId($userId);
        $products = $this->loadProducts($storeId);

        return [
            'store_id' => $storeId,
            'categories' => $this->loadCategories(),
            'products' => $products,
            'inventory' => $this->buildInventoryMetrics($products),
        ];
    }

    public function createProduct(string $userId, array $data): array
    {
        $storeId = $this->resolveStoreId($userId);
        $this->assertCategoryExists($data['category_id']);

        $statement = $this->pdo->prepare(
            'INSERT INTO products (
                id, store_id, category_id, name, description, sku, base_price, currency, status,
                stock_quantity, min_stock_quantity, image_path
             ) VALUES (
                gen_random_uuid(), :store_id, :category_id, :name, :description, :sku, :base_price, :currency, :status,
                :stock_quantity, :min_stock_quantity, :image_path
             )'
        );

        try {
            $statement->execute([
                'store_id' => $storeId,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'sku' => $data['sku'],
                'base_price' => $data['base_price'],
                'currency' => $data['currency'],
                'status' => $data['status'],
                'stock_quantity' => $data['stock_quantity'],
                'min_stock_quantity' => $data['min_stock_quantity'],
                'image_path' => $data['image_path'] !== '' ? $data['image_path'] : null,
            ]);
        } catch (\PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'unique')) {
                throw new ApiException(422, 'PRODUCT_SKU_UNAVAILABLE', 'Ja existe um produto com este SKU nesta loja.');
            }

            throw $exception;
        }

        return $this->listCatalog($userId);
    }

    public function updateProduct(string $userId, string $productId, array $data): array
    {
        $storeId = $this->resolveStoreId($userId);
        $this->assertCategoryExists($data['category_id']);

        $current = $this->findProductRow($storeId, $productId);
        if (!$current) {
            throw new ApiException(404, 'PRODUCT_NOT_FOUND', 'Produto nao encontrado para esta loja.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE products
             SET category_id = :category_id,
                 name = :name,
                 description = :description,
                 sku = :sku,
                 base_price = :base_price,
                 currency = :currency,
                 status = :status,
                 stock_quantity = :stock_quantity,
                 min_stock_quantity = :min_stock_quantity,
                 image_path = :image_path,
                 updated_at = NOW()
             WHERE id = :product_id AND store_id = :store_id'
        );

        try {
            $statement->execute([
                'product_id' => $productId,
                'store_id' => $storeId,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'sku' => $data['sku'],
                'base_price' => $data['base_price'],
                'currency' => $data['currency'],
                'status' => $data['status'],
                'stock_quantity' => $data['stock_quantity'],
                'min_stock_quantity' => $data['min_stock_quantity'],
                'image_path' => $data['image_path'] !== '' ? $data['image_path'] : null,
            ]);
        } catch (\PDOException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'unique')) {
                throw new ApiException(422, 'PRODUCT_SKU_UNAVAILABLE', 'Ja existe um produto com este SKU nesta loja.');
            }

            throw $exception;
        }

        if (
            (int) $current['stock_quantity'] !== (int) $data['stock_quantity'] ||
            (int) $current['min_stock_quantity'] !== (int) $data['min_stock_quantity'] ||
            (string) $current['status'] !== (string) $data['status']
        ) {
            $movement = $this->pdo->prepare(
                'INSERT INTO inventory_movements (
                    id, product_id, movement_type, quantity_before, quantity_after, note, actor_user_id
                 ) VALUES (
                    gen_random_uuid(), :product_id, :movement_type, :quantity_before, :quantity_after, :note, :actor_user_id
                 )'
            );
            $movement->execute([
                'product_id' => $productId,
                'movement_type' => 'manual_adjustment',
                'quantity_before' => (int) $current['stock_quantity'],
                'quantity_after' => (int) $data['stock_quantity'],
                'note' => 'Ajuste de produto via catalogo',
                'actor_user_id' => $userId,
            ]);
        }

        return $this->listCatalog($userId);
    }

    public function updateInventory(string $userId, string $productId, array $data): array
    {
        $storeId = $this->resolveStoreId($userId);
        $current = $this->findProductRow($storeId, $productId);

        if (!$current) {
            throw new ApiException(404, 'PRODUCT_NOT_FOUND', 'Produto nao encontrado para esta loja.');
        }

        $nextStock = (int) ($data['stock_quantity'] ?? $current['stock_quantity']);
        $nextMinStock = (int) ($data['min_stock_quantity'] ?? $current['min_stock_quantity']);
        $nextStatus = (string) ($data['status'] ?? $current['status']);

        $this->pdo->beginTransaction();
        try {
            $update = $this->pdo->prepare(
                'UPDATE products
                 SET stock_quantity = :stock_quantity,
                     min_stock_quantity = :min_stock_quantity,
                     status = :status,
                     updated_at = NOW()
                 WHERE id = :product_id AND store_id = :store_id'
            );
            $update->execute([
                'product_id' => $productId,
                'store_id' => $storeId,
                'stock_quantity' => $nextStock,
                'min_stock_quantity' => $nextMinStock,
                'status' => $nextStatus,
            ]);

            $movement = $this->pdo->prepare(
                'INSERT INTO inventory_movements (
                    id, product_id, movement_type, quantity_before, quantity_after, note, actor_user_id
                 ) VALUES (
                    gen_random_uuid(), :product_id, :movement_type, :quantity_before, :quantity_after, :note, :actor_user_id
                 )'
            );
            $movement->execute([
                'product_id' => $productId,
                'movement_type' => 'manual_adjustment',
                'quantity_before' => (int) $current['stock_quantity'],
                'quantity_after' => $nextStock,
                'note' => (string) ($data['note'] ?? 'Ajuste manual via Partner Portal'),
                'actor_user_id' => $userId,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        $products = $this->loadProducts($storeId);
        $updated = null;
        foreach ($products as $product) {
            if ($product['id'] === $productId) {
                $updated = $product;
                break;
            }
        }

        return [
            'product' => $updated,
            'categories' => $this->loadCategories(),
            'inventory' => $this->buildInventoryMetrics($products),
            'products' => $products,
        ];
    }

    private function resolveStoreId(string $userId): string
    {
        $statement = $this->pdo->prepare(
            'SELECT s.id
             FROM partner_accounts ap
             INNER JOIN stores s ON s.partner_account_id = ap.id
             WHERE ap.owner_user_id = :user_id
             LIMIT 1'
        );
        $statement->execute(['user_id' => $userId]);
        $storeId = $statement->fetchColumn();

        if (!$storeId) {
            throw new ApiException(404, 'PARTNER_STORE_NOT_FOUND', 'Nao foi possivel localizar a loja do parceiro.');
        }

        return (string) $storeId;
    }

    private function loadProducts(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT p.id, p.name, p.description, p.sku, p.base_price, p.currency, p.status, p.stock_quantity,
                    p.min_stock_quantity, p.sold_count, p.image_path, c.name AS category_name, c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.store_id = :store_id
             ORDER BY p.created_at DESC'
        );
        $statement->execute(['store_id' => $storeId]);

        return array_map(
            fn (array $row) => $this->formatProduct($row),
            $statement->fetchAll() ?: []
        );
    }

    private function loadCategories(): array
    {
        $statement = $this->pdo->query(
            "SELECT id, slug, name
             FROM categories
             WHERE status = 'active'
             ORDER BY name"
        );

        return array_map(
            static fn (array $row) => [
                'id' => $row['id'],
                'slug' => $row['slug'],
                'name' => $row['name'],
            ],
            $statement->fetchAll() ?: []
        );
    }

    private function formatProduct(array $row): array
    {
        $stock = (int) $row['stock_quantity'];
        $minStock = (int) $row['min_stock_quantity'];
        $inventoryState = $stock <= 0 ? 'out' : ($stock <= $minStock ? 'low' : 'normal');

        return [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'sku' => $row['sku'],
            'price' => 'R$ ' . number_format((float) $row['base_price'], 2, ',', '.'),
            'base_price' => (float) $row['base_price'],
            'currency' => $row['currency'],
            'status' => $row['status'],
            'category' => $row['category_name'] ?? 'Sem categoria',
            'category_slug' => $row['category_slug'] ?? 'sem-categoria',
            'stock_quantity' => $stock,
            'min_stock_quantity' => $minStock,
            'sold_count' => (int) $row['sold_count'],
            'image_path' => $row['image_path'],
            'inventory_state' => $inventoryState,
        ];
    }

    private function assertCategoryExists(string $categoryId): void
    {
        $statement = $this->pdo->prepare(
            "SELECT 1
             FROM categories
             WHERE id = :category_id AND status = 'active'
             LIMIT 1"
        );
        $statement->execute(['category_id' => $categoryId]);

        if (!$statement->fetchColumn()) {
            throw new ApiException(422, 'CATEGORY_NOT_FOUND', 'Categoria invalida para o produto.');
        }
    }

    private function findProductRow(string $storeId, string $productId): array|false
    {
        $select = $this->pdo->prepare(
            'SELECT id, stock_quantity, min_stock_quantity, status
             FROM products
             WHERE id = :product_id AND store_id = :store_id
             LIMIT 1'
        );
        $select->execute([
            'product_id' => $productId,
            'store_id' => $storeId,
        ]);

        return $select->fetch();
    }

    private function buildInventoryMetrics(array $products): array
    {
        $low = 0;
        $paused = 0;
        $normal = 0;

        foreach ($products as $product) {
            if ($product['status'] === 'paused') {
                $paused++;
            }

            if ($product['inventory_state'] === 'out' || $product['inventory_state'] === 'low') {
                $low++;
            } else {
                $normal++;
            }
        }

        return [
            'low_stock_count' => $low,
            'paused_count' => $paused,
            'normal_count' => $normal,
            'review_sla' => '15 min',
        ];
    }
}
