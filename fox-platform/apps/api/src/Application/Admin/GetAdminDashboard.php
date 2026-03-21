<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class GetAdminDashboard
{
    public function __construct(
        private readonly AdminOperationsRepository $operations
    ) {
    }

    public function __invoke(): array
    {
        return $this->operations->getDashboard();
    }
}
