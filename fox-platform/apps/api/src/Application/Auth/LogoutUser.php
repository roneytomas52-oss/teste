<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Auth;

use FoxPlatform\Api\Domain\Identity\RefreshSessionRepository;

class LogoutUser
{
    public function __construct(
        private readonly RefreshSessionRepository $refreshSessions
    ) {
    }

    public function __invoke(string $refreshToken): void
    {
        $this->refreshSessions->revokeByTokenHash(hash('sha256', $refreshToken));
    }
}
