<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Identity;

interface UserRepository
{
    public function findByEmail(string $email): ?User;

    public function findById(string $id): ?User;

    public function getRolesForUser(string $userId): array;

    public function getPermissionsForUser(string $userId): array;

    public function updatePassword(string $userId, string $passwordHash): void;
}
