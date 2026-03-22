<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class ApproveDriverApproval
{
    public function __construct(
        private readonly AdminOperationsRepository $operations
    ) {
    }

    public function __invoke(string $userId, string $driverId): array
    {
        return $this->operations->reviewDriverApproval($userId, $driverId, 'approve');
    }
}
