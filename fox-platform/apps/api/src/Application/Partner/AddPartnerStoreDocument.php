<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerPortalRepository;

class AddPartnerStoreDocument
{
    public function __construct(
        private readonly PartnerPortalRepository $partners
    ) {
    }

    public function __invoke(string $userId, array $document): array
    {
        return $this->partners->addStoreDocument($userId, $document);
    }
}
