<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Admin;

interface AdminOperationsRepository
{
    public function getDashboard(): array;

    public function getOrders(): array;

    public function getOrderDetail(string $orderId): array;

    public function getPartnerApprovals(): array;

    public function getDriverApprovals(): array;

    public function getFinance(): array;

    public function getSupport(): array;

    public function getSupportThread(string $ticketId): array;

    public function replySupportThread(string $userId, string $ticketId, array $data): array;

    public function updateSupportTicketStatus(string $userId, string $ticketId, array $data): array;

    public function getSettings(): array;

    public function updateSettings(string $userId, array $settings): array;

    public function reviewPartnerApproval(string $partnerId, string $decision): array;

    public function reviewDriverApproval(string $driverId, string $decision): array;
}
