<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerPortalRepository;

class GetPartnerTeam
{
    public function __construct(
        private readonly PartnerPortalRepository $repository
    ) {
    }

    public function __invoke(string $userId): array
    {
        return $this->repository->getTeam($userId);
    }
}
