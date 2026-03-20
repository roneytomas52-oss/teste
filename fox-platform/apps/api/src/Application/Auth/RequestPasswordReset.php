<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Auth;

use FoxPlatform\Api\Domain\Identity\PasswordResetTokenRepository;
use FoxPlatform\Api\Domain\Identity\UserRepository;
use FoxPlatform\Api\Infrastructure\Support\Clock;
use FoxPlatform\Api\Infrastructure\Support\UuidGenerator;

class RequestPasswordReset
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordResetTokenRepository $passwordResetTokens,
        private readonly UuidGenerator $uuidGenerator,
        private readonly Clock $clock,
        private readonly array $config
    ) {
    }

    public function __invoke(string $email): array
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            return ['accepted' => true];
        }

        $resetTtl = (int) ($this->config['tokens']['password_reset_ttl_seconds'] ?? 3600);
        $token = str_replace('-', '', $this->uuidGenerator->uuid());

        $this->passwordResetTokens->create(
            $email,
            hash('sha256', $token),
            $this->clock->now()->modify(sprintf('+%d seconds', $resetTtl))
        );

        $response = ['accepted' => true];
        if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL)) {
            $response['debug_reset_token'] = $token;
        }

        return $response;
    }
}
