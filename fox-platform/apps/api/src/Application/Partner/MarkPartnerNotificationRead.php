<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerOperationsRepository;

class MarkPartnerNotificationRead
{
    public function __construct(
        private readonly PartnerOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $notificationId): array
    {
        return $this->repository->markNotificationRead($userId, $notificationId);
    }
}
