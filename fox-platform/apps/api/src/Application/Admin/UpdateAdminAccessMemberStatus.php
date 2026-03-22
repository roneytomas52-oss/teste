<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class UpdateAdminAccessMemberStatus
{
    public function __construct(
        private readonly AdminOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $memberId, array $data): array
    {
        return $this->repository->updateAccessMemberStatus($userId, $memberId, $data);
    }
}
