<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Customer;

use FoxPlatform\Api\Domain\Customer\CustomerPortalRepository;
use FoxPlatform\Api\Domain\Identity\PasswordHasher;

class RegisterCustomer
{
    public function __construct(
        private readonly CustomerPortalRepository $repository,
        private readonly PasswordHasher $passwordHasher
    ) {
    }

    public function __invoke(array $data): array
    {
        $data['password_hash'] = $this->passwordHasher->hash($data['password']);
        unset($data['password']);

        return $this->repository->registerCustomer($data);
    }
}
