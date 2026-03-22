<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Admin;

use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class GetAdminPartnerApprovalDetail
{
    public function __construct(
        private readonly AdminOperationsRepository $repository
    ) {
    }

    public function __invoke(string $partnerId): array
    {
        return $this->repository->getPartnerApprovalDetail($partnerId);
    }
}
