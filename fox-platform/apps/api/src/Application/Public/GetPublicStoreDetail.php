<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Public;

use FoxPlatform\Api\Domain\Public\PublicLandingRepository;

class GetPublicStoreDetail
{
    public function __construct(
        private readonly PublicLandingRepository $publicLanding
    ) {
    }

    public function __invoke(string $storeId): array
    {
        return $this->publicLanding->getStoreDetail($storeId);
    }
}
