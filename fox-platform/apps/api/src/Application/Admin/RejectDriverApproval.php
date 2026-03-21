<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class RejectDriverApproval
{
    public function __construct(
        private readonly AdminOperationsRepository $operations
    ) {
    }

    public function __invoke(string $driverId): array
    {
        return $this->operations->reviewDriverApproval($driverId, 'reject');
    }
}
