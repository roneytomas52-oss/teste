<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerPortalRepository;

class GetPartnerStore
{
    public function __construct(
        private readonly PartnerPortalRepository $partners
    ) {
    }

    public function __invoke(string $userId): array
    {
        return $this->partners->getStore($userId);
    }
}
