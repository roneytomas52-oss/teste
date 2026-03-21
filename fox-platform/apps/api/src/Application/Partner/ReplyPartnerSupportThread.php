<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Partner;

use FoxPlatform\Api\Domain\Partner\PartnerOperationsRepository;

class ReplyPartnerSupportThread
{
    public function __construct(
        private readonly PartnerOperationsRepository $repository
    ) {
    }

    public function __invoke(string $userId, string $ticketId, array $data): array
    {
        return $this->repository->replySupportThread($userId, $ticketId, $data);
    }
}
