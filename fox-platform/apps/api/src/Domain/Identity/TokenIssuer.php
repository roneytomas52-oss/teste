<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Identity;

interface TokenIssuer
{
    public function issueAccessToken(array $claims): string;

    public function verifyAccessToken(string $token): ?array;
}
