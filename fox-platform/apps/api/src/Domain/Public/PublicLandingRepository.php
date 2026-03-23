<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Public;

interface PublicLandingRepository
{
    public function getCategories(): array;

    public function getPlatformMetrics(): array;

    public function getStores(array $filters = []): array;

    public function getStoreDetail(string $storeId): array;

    public function createPublicOrder(array $data): array;

    public function getPublicOrderTracking(string $orderNumber): array;

    public function createPartnerLead(array $data): array;

    public function createDriverLead(array $data): array;
}
