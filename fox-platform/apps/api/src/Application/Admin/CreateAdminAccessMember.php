<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class CreateAdminAccessMember
{
    public function __construct(
        private readonly AdminOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId, array $data): array
    {
        return $this->repository->createAccessMember($userId, $data);
    }
}
