<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerCatalogRepository;

class UpdatePartnerProduct
{
    public function __construct(
        private readonly PartnerCatalogRepository $catalog
    ) {
    }

    public function __invoke(string $userId, string $productId, array $data): array
    {
        return $this->catalog->updateProduct($userId, $productId, $data);
    }
}
