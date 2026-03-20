<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerPortalRepository;

class UpdatePartnerProfile
{
    public function __construct(
        private readonly PartnerPortalRepository $partners
    ) {
    }

    public function __invoke(string $userId, array $data): array
    {
        return $this->partners->updateProfile($userId, $data);
    }
}
