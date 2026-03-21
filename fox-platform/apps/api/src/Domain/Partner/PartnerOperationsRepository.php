<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Partner;

interface PartnerOperationsRepository
{
    public function getDashboard(string $userId): array;

    public function getOrders(string $userId): array;

    public function updateOrderStatus(string $userId, string $orderId, array $data): array;
}
