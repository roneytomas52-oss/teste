<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerPortalRepository;

class ReplacePartnerStoreHours
{
    public function __construct(
        private readonly PartnerPortalRepository $partners
    ) {
    }

    public function __invoke(string $userId, array $hours): array
    {
        return $this->partners->replaceStoreHours($userId, $hours);
    }
}
