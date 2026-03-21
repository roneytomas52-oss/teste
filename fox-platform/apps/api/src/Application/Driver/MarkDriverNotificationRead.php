<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Driver;

use FoxPlatform\Api\Domain\Driver\DriverPortalRepository;

class MarkDriverNotificationRead
{
    public function __construct(
        private readonly DriverPortalRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $notificationId): array
    {
        return $this->repository->markNotificationRead($userId, $notificationId);
    }
}
