<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class ResolveAdminDriverApproval
{
    public function __construct(
        private readonly AdminOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $driverId, array $data): array
    {
        return $this->repository->resolveDriverApproval($userId, $driverId, $data);
    }
}
