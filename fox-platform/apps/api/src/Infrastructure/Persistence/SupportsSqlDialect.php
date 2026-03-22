<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Infrastructure\Support\UuidGenerator;

trait SupportsSqlDialect
{
    private ?string $resolvedDriver = null;

    protected function driver(): string
    {
        if ($this->resolvedDriver !== null) {
            return $this->resolvedDriver;
        }

        /** @var PDO $pdo */
        $pdo = $this->pdo;
        $this->resolvedDriver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        return $this->resolvedDriver;
    }

    protected function isMySql(): bool
    {
        return $this->driver() === 'mysql';
    }

    protected function uuidExpression(): string
    {
        return $this->isMySql() ? 'UUID()' : 'gen_random_uuid()';
    }

    protected function newUuid(): string
    {
        return (new UuidGenerator())->uuid();
    }

    protected function jsonPlaceholder(string $placeholder): string
    {
        return $this->isMySql() ? $placeholder : $placeholder . '::jsonb';
    }

    protected function dateExpression(string $column): string
    {
        return $this->isMySql() ? sprintf('DATE(%s)', $column) : sprintf('%s::date', $column);
    }

    protected function nowPlusDays(int $days): string
    {
        return $this->isMySql()
            ? sprintf('DATE_ADD(NOW(), INTERVAL %d DAY)', $days)
            : sprintf("NOW() + INTERVAL '%d days'", $days);
    }

    protected function nowMinusMinutes(int $minutes): string
    {
        return $this->isMySql()
            ? sprintf('DATE_SUB(NOW(), INTERVAL %d MINUTE)', $minutes)
            : sprintf("NOW() - INTERVAL '%d minutes'", $minutes);
    }

    protected function currentDatePlusDays(int $days): string
    {
        return $this->isMySql()
            ? sprintf('DATE_ADD(CURRENT_DATE, INTERVAL %d DAY)', $days)
            : sprintf("CURRENT_DATE + INTERVAL '%d days'", $days);
    }
}
