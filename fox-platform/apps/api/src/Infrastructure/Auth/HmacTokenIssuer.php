<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Auth;

use FoxPlatform\Api\Domain\Identity\TokenIssuer;

class HmacTokenIssuer implements TokenIssuer
{
    public function __construct(
        private readonly array $config
    ) {
    }

    public function issueAccessToken(array $claims): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $secret = $this->secret();

        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64UrlEncode(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public function verifyAccessToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        $expected = $this->base64UrlEncode(hash_hmac('sha256', $header . '.' . $payload, $this->secret(), true));

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $decoded = json_decode($this->base64UrlDecode($payload), true);
        if (!is_array($decoded)) {
            return null;
        }

        if (($decoded['exp'] ?? 0) < time()) {
            return null;
        }

        return $decoded;
    }

    private function secret(): string
    {
        return (string) ($this->config['tokens']['access_token_secret'] ?? 'fox-platform-local-secret');
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($value, '-_', '+/')) ?: '';
    }
}
