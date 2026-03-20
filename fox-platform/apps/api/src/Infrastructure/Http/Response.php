<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Http;

class Response
{
    public function __construct(
        private int $status,
        private string $body = '',
        private array $headers = []
    ) {
    }

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        return new self(
            $status,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            array_merge(['Content-Type' => 'application/json; charset=utf-8'], $headers)
        );
    }

    public static function empty(int $status = 204, array $headers = []): self
    {
        return new self($status, '', $headers);
    }

    public function withHeader(string $name, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }

        echo $this->body;
    }
}
