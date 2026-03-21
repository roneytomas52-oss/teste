<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerPortalRepository;

class UpdatePartnerTeamMemberStatus
{
    public function __construct(
        private readonly PartnerPortalRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $memberId, array $data): array
    {
        return $this->repository->updateTeamMemberStatus($userId, $memberId, $data);
    }
}
