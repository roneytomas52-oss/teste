<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class GetAdminDriverApprovalDetail
{
    public function __construct(
        private readonly AdminOperationsRepository $repository
    ) {
    }

    public function __invoke(string $driverId): array
    {
        return $this->repository->getDriverApprovalDetail($driverId);
    }
}
