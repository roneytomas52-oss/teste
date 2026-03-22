<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Partner;

interface PartnerOperationsRepository
{
    public function getDashboard(string $userId): array;

    public function getOrders(string $userId): array;

    public function getOrderDetail(string $userId, string $orderId): array;

    public function updateOrderStatus(string $userId, string $orderId, array $data): array;

    public function getFinance(string $userId): array;

    public function getSupport(string $userId): array;

    public function createSupportTicket(string $userId, array $data): array;

    public function getSupportThread(string $userId, string $ticketId): array;

    public function replySupportThread(string $userId, string $ticketId, array $data): array;

    public function getNotifications(string $userId): array;

    public function markNotificationRead(string $userId, string $notificationId): array;
}
