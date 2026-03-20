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
}
