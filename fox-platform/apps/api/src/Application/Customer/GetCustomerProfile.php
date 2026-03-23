<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Customer;

use FoxPlatform\Api\Domain\Customer\CustomerPortalRepository;

class GetCustomerProfile
{
    public function __construct(
        private readonly CustomerPortalRepository $repository
    ) {
    }

    public function __invoke(string $userId): array
    {
        return $this->repository->getProfile($userId);
    }
}
