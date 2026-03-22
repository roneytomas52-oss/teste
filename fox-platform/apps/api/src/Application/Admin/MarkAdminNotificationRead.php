<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class MarkAdminNotificationRead
{
    public function __construct(
        private readonly AdminOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $notificationId): array
    {
        return $this->repository->markNotificationRead($userId, $notificationId);
    }
}
