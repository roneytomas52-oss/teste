<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Public;

use FoxPlatform\Api\Domain\Public\PublicLandingRepository;

class GetPublicOrderTracking
{
    public function __construct(
        private readonly PublicLandingRepository $publicLanding
    ) {
    }

    public function __invoke(string $orderNumber): array
    {
        return $this->publicLanding->getPublicOrderTracking($orderNumber);
    }
}
