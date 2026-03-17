<?php

declare(strict_types=1);

date_default_timezone_set(env('APP_TIMEZONE', 'America/Sao_Paulo'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function env(string $key, ?string $default = null): ?string
{
    static $env = null;

    if ($env === null) {
        $env = [];
        $envPath = dirname(__DIR__) . '/.env';
        if (is_file($envPath)) {
            foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                $v = trim($v, "\"'");
                $env[$k] = $v;
            }
        }
    }

    return $env[$key] ?? $default;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    $base = rtrim(env('APP_URL', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function sixammart_url(string $path = ''): string
{
    $base = rtrim(env('SIXAMMART_BASE_URL', ''), '/');
    return $base . '/' . ltrim($path, '/');
}
