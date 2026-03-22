<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class UpdateAdminAccessMember
{
    public function __construct(
        private readonly AdminOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $memberId, array $data): array
    {
        return $this->repository->updateAccessMember($userId, $memberId, $data);
    }
}
