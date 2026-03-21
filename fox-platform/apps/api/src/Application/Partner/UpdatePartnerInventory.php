<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerCatalogRepository;

class UpdatePartnerInventory
{
    public function __construct(
        private readonly PartnerCatalogRepository $catalog
    ) {
    }

    public function __invoke(string $userId, string $productId, array $data): array
    {
        return $this->catalog->updateInventory($userId, $productId, $data);
    }
}
