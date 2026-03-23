<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Customer;

interface CustomerPortalRepository
{
    public function registerCustomer(array $data): array;

    public function getProfile(string $userId): array;

    public function updateProfile(string $userId, array $data): array;

    public function getOrders(string $userId): array;

    public function getOrderDetail(string $userId, string $orderId): array;

    public function createOrder(string $userId, array $data): array;
}
