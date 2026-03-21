<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Driver;

interface DriverPortalRepository
{
    public function getDashboard(string $userId): array;

    public function getProfile(string $userId): array;

    public function updateProfile(string $userId, array $data): array;

    public function getEarnings(string $userId): array;

    public function getAvailability(string $userId): array;

    public function getDocuments(string $userId): array;

    public function getSupport(string $userId): array;

    public function createSupportTicket(string $userId, array $data): array;

    public function getSupportThread(string $userId, string $ticketId): array;

    public function replySupportThread(string $userId, string $ticketId, array $data): array;

    public function getNotifications(string $userId): array;

    public function markNotificationRead(string $userId, string $notificationId): array;
}
