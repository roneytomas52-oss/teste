<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Public;

use FoxPlatform\Api\Domain\Public\PublicLandingRepository;

class CreatePartnerLead
{
    public function __construct(
        private readonly PublicLandingRepository $publicLanding
    ) {
    }

    public function __invoke(array $data): array
    {
        return $this->publicLanding->createPartnerLead($data);
    }
}
