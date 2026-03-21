<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerCatalogRepository;

class CreatePartnerProduct
{
    public function __construct(
        private readonly PartnerCatalogRepository $catalog
    ) {
    }

    public function __invoke(string $userId, array $data): array
    {
        return $this->catalog->createProduct($userId, $data);
    }
}
