<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerOperationsRepository;

class UpdatePartnerOrderStatus
{
    public function __construct(
        private readonly PartnerOperationsRepository $operations
    ) {
    }

    public function __invoke(string $userId, string $orderId, array $data): array
    {
        return $this->operations->updateOrderStatus($userId, $orderId, $data);
    }
}
