<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Admin;

interface AdminOperationsRepository
{
    public function getDashboard(): array;

    public function getOrders(): array;

    public function getOrderDetail(string $orderId): array;

    public function updateOrderStatus(string $userId, string $orderId, array $data): array;

    public function addOrderNote(string $userId, string $orderId, array $data): array;

    public function getPartnerApprovals(): array;

    public function getFinance(): array;

    public function getAnalytics(): array;

    public function getReports(): array;

    public function getSupport(): array;

    public function getSupportThread(string $ticketId): array;

    public function replySupportThread(string $userId, string $ticketId, array $data): array;

    public function updateSupportTicketStatus(string $userId, string $ticketId, array $data): array;

    public function getSettings(): array;

    public function updateSettings(string $userId, array $settings): array;

    public function getAccess(): array;

    public function createAccessMember(string $userId, array $data): array;

    public function updateAccessMember(string $userId, string $memberId, array $data): array;

    public function updateAccessMemberStatus(string $userId, string $memberId, array $data): array;

    public function getNotifications(string $userId): array;

    public function markNotificationRead(string $userId, string $notificationId): array;

    public function getPartnerApprovalDetail(string $partnerId): array;

    public function reviewPartnerApproval(string $userId, string $partnerId, string $decision): array;

    public function resolvePartnerApproval(string $userId, string $partnerId, array $data): array;

    public function getDriverApprovals(): array;

    public function getDriverApprovalDetail(string $driverId): array;

    public function reviewDriverApproval(string $userId, string $driverId, string $decision): array;

    public function resolveDriverApproval(string $userId, string $driverId, array $data): array;
}
