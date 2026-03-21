<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerOperationsRepository;

class GetPartnerNotifications
{
    public function __construct(
        private readonly PartnerOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId): array
    {
        return $this->repository->getNotifications($userId);
    }
}
