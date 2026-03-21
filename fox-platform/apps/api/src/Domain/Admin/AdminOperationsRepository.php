<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Admin;

interface AdminOperationsRepository
{
    public function getDashboard(): array;

    public function getOrders(): array;

    public function getPartnerApprovals(): array;

    public function getDriverApprovals(): array;
}
