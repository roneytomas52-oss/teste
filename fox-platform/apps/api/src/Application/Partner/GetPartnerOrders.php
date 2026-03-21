<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerOperationsRepository;

class GetPartnerOrders
{
    public function __construct(
        private readonly PartnerOperationsRepository $operations
    ) {
    }

    public function __invoke(string $userId): array
    {
        return $this->operations->getOrders($userId);
    }
}
