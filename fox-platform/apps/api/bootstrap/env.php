<?php

declare(strict_types=1);

function fox_api_parse_env_file(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $values = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
        $name = trim($name);
        $value = trim($value);

        if ($name === '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $values[$name] = $value;
    }

    return $values;
}

function fox_api_load_env(string $apiRoot): array
{
    $sources = [
        $apiRoot . '/.env',
        dirname($apiRoot) . '/.env',
        $apiRoot . '/.env.example',
        dirname($apiRoot) . '/.env.example',
    ];

    $loaded = [];

    foreach ($sources as $source) {
        foreach (fox_api_parse_env_file($source) as $key => $value) {
            if (array_key_exists($key, $loaded)) {
                continue;
            }

            $loaded[$key] = $value;
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv(sprintf('%s=%s', $key, $value));
        }
    }

    return $loaded;
}
