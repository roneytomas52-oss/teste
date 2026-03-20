<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Support;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        private readonly int $status,
        private readonly string $codeName,
        string $message,
        private readonly array $details = []
    ) {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function codeName(): string
    {
        return $this->codeName;
    }

    public function details(): array
    {
        return $this->details;
    }
}
