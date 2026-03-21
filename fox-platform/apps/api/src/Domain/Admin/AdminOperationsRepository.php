<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Admin;

interface AdminOperationsRepository
{
    public function getDashboard(): array;

    public function getOrders(): array;

    public function getPartnerApprovals(): array;

    public function getDriverApprovals(): array;

    public function getFinance(): array;

    public function getSupport(): array;

    public function reviewPartnerApproval(string $partnerId, string $decision): array;

    public function reviewDriverApproval(string $driverId, string $decision): array;
}
