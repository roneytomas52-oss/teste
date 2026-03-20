<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Auth;

use FoxPlatform\Api\Domain\Identity\PasswordHasher;
use FoxPlatform\Api\Domain\Identity\PasswordResetTokenRepository;
use FoxPlatform\Api\Domain\Identity\RefreshSessionRepository;
use FoxPlatform\Api\Domain\Identity\UserRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class ResetPassword
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordResetTokenRepository $passwordResetTokens,
        private readonly RefreshSessionRepository $refreshSessions,
        private readonly PasswordHasher $passwordHasher
    ) {
    }

    public function __invoke(string $email, string $token, string $newPassword): void
    {
        $user = $this->users->findByEmail($email);
        if (!$user) {
            throw new ApiException(404, 'AUTH_USER_NOT_FOUND', 'Usuario nao encontrado para redefinicao.');
        }

        $isValid = $this->passwordResetTokens->existsValid($email, hash('sha256', $token));
        if (!$isValid) {
            throw new ApiException(422, 'AUTH_RESET_TOKEN_INVALID', 'Token de redefinicao invalido ou expirado.');
        }

        $this->users->updatePassword($user->id, $this->passwordHasher->hash($newPassword));
        $this->passwordResetTokens->deleteByEmail($email);
        $this->refreshSessions->revokeAllByUserId($user->id);
    }
}
