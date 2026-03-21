<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Partner;

interface PartnerCatalogRepository
{
    public function listCatalog(string $userId): array;

    public function createProduct(string $userId, array $data): array;

    public function updateProduct(string $userId, string $productId, array $data): array;

    public function updateInventory(string $userId, string $productId, array $data): array;
}
