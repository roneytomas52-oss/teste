<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Support;

use DateTimeImmutable;
use DateTimeZone;

class Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone($_ENV['APP_TIMEZONE'] ?? 'UTC'));
    }
}
