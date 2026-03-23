<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Customer;

use FoxPlatform\Api\Domain\Customer\CustomerPortalRepository;

class GetCustomerOrderDetail
{
    public function __construct(
        private readonly CustomerPortalRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $orderId): array
    {
        return $this->repository->getOrderDetail($userId, $orderId);
    }
}
