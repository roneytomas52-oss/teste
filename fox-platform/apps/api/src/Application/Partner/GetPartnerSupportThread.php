<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerOperationsRepository;

class GetPartnerSupportThread
{
    public function __construct(
        private readonly PartnerOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $ticketId): array
    {
        return $this->repository->getSupportThread($userId, $ticketId);
    }
}
