<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Partner;

interface PartnerPortalRepository
{
    public function getProfile(string $userId): array;

    public function updateProfile(string $userId, array $data): array;

    public function getStore(string $userId): array;

    public function updateStore(string $userId, array $data): array;

    public function replaceStoreHours(string $userId, array $hours): array;

    public function addStoreDocument(string $userId, array $document): array;

    public function getTeam(string $userId): array;

    public function createTeamMember(string $userId, array $data): array;

    public function updateTeamMember(string $userId, string $memberId, array $data): array;

    public function updateTeamMemberStatus(string $userId, string $memberId, array $data): array;
}
