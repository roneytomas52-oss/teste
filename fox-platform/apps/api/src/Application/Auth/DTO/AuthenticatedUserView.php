<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Application\Auth\DTO;

use FoxPlatform\Api\Domain\Identity\User;

class AuthenticatedUserView
{
    public function __construct(
        public readonly User $user,
        public readonly array $roles,
        public readonly array $permissions,
        public readonly string $guard
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->fullName,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'status' => $this->user->status,
            'locale' => $this->user->locale,
            'guard' => $this->guard,
            'roles' => array_values(array_map(
                static fn (array $role) => [
                    'slug' => $role['slug'],
                    'scope' => $role['scope'],
                    'name' => $role['name'],
                ],
                $this->roles
            )),
            'permissions' => array_values(array_map(
                static fn (array $permission) => $permission['slug'],
                $this->permissions
            )),
        ];
    }
}
