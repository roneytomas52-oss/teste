<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

final class GetAdminReports
{
    public function __construct(
        private readonly AdminOperationsRepository $repository
    ) {
    }

    public function __invoke(): array
    {
        return $this->repository->getReports();
    }
}
