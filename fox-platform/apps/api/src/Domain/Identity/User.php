<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Domain\Identity;

class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $fullName,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly string $passwordHash,
        public readonly string $status,
        public readonly string $locale
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) $data['id'],
            (string) $data['full_name'],
            (string) $data['email'],
            $data['phone'] ?? null,
            (string) $data['password_hash'],
            (string) $data['status'],
            (string) ($data['locale'] ?? 'pt_BR')
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
