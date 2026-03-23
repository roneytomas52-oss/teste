<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Customer;

use FoxPlatform\Api\Domain\Customer\CustomerPortalRepository;

class GetCustomerOrders
{
    public function __construct(
        private readonly CustomerPortalRepository $repository
    ) {
    }

    public function __invoke(string $userId): array
    {
        return $this->repository->getOrders($userId);
    }
}
