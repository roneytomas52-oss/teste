<?php

declare(strict_types=1);

function fetch_remote_html(string $url): ?string
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 12,
            'header' => "User-Agent: FoxLandingStandalone/1.0\r\n",
        ],
    ]);

    $html = @file_get_contents($url, false, $context);
    if (is_string($html) && $html !== '') {
        return $html;
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'FoxLandingStandalone/1.0',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if (is_string($response) && $response !== '') {
            return $response;
        }
    }

    return null;
}

function absolutize_embed_urls(string $html, string $baseUrl): string
{
    $base = rtrim($baseUrl, '/');

    $html = preg_replace_callback('/\b(href|src|action)=(["\'])(.*?)\2/i', static function (array $m) use ($base): string {
        $attr = $m[1];
        $quote = $m[2];
        $value = trim($m[3]);

        if ($value === '' || str_starts_with($value, '#') || str_starts_with($value, 'javascript:') || str_starts_with($value, 'data:')) {
            return $m[0];
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return sprintf('%s=%s%s%s', $attr, $quote, $value, $quote);
        }

        if (str_starts_with($value, '//')) {
            return sprintf('%s=%shttps:%s%s', $attr, $quote, $value, $quote);
        }

        if (str_starts_with($value, '/')) {
            return sprintf('%s=%s%s%s%s', $attr, $quote, $base, $value, $quote);
        }

        return sprintf('%s=%s%s/%s%s', $attr, $quote, $base, ltrim($value, './'), $quote);
    }, $html) ?? $html;

    return $html;
}

function build_embedded_official_form(string $url): array
{
    $html = fetch_remote_html($url);
    if ($html === null) {
        return [null, 'Não foi possível carregar o formulário oficial agora.'];
    }

    $body = $html;
    if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $matches)) {
        $body = $matches[1];
    }

    $styles = [];
    if (preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $matches)) {
        $styles = $matches[0];
    }

    $headScripts = [];
    if (preg_match_all('/<script[^>]+src=["\'][^"\']+["\'][^>]*><\/script>/i', $html, $matches)) {
        $headScripts = $matches[0];
    }

    $composed = implode("\n", $styles) . "\n" . $body . "\n" . implode("\n", $headScripts);

    return [absolutize_embed_urls($composed, $url), null];
}
