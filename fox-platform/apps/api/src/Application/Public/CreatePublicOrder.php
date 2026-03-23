<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Public;

use FoxPlatform\Api\Domain\Public\PublicLandingRepository;

class CreatePublicOrder
{
    public function __construct(
        private readonly PublicLandingRepository $publicLanding
    ) {
    }

    public function __invoke(array $payload): array
    {
        return $this->publicLanding->createPublicOrder($payload);
    }
}
