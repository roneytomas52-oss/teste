<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Auth;

use FoxPlatform\Api\Infrastructure\Http\Request;

class BearerTokenParser
{
    public function extract(Request $request): ?string
    {
        $header = (string) $request->header('authorization', '');
        if ($header === '' || !str_starts_with(strtolower($header), 'bearer ')) {
            return null;
        }

        return trim(substr($header, 7));
    }
}
