<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class RejectPartnerApproval
{
    public function __construct(
        private readonly AdminOperationsRepository $operations
    ) {
    }

    public function __invoke(string $partnerId): array
    {
        return $this->operations->reviewPartnerApproval($partnerId, 'reject');
    }
}
