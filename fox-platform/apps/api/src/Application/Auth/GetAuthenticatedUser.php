<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Auth;

use FoxPlatform\Api\Application\Auth\DTO\AuthenticatedUserView;
use FoxPlatform\Api\Domain\Identity\UserRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class GetAuthenticatedUser
{
    public function __construct(
        private readonly UserRepository $users
    ) {
    }

    public function __invoke(array $authContext): AuthenticatedUserView
    {
        $user = $this->users->findById($authContext['user_id']);
        if (!$user) {
            throw new ApiException(404, 'AUTH_USER_NOT_FOUND', 'Usuario autenticado nao encontrado.');
        }

        $roles = $this->users->getRolesForUser($authContext['user_id']);
        $permissions = $this->users->getPermissionsForUser($authContext['user_id']);

        return new AuthenticatedUserView(
            $user,
            $roles,
            $permissions,
            $authContext['guard']
        );
    }
}
