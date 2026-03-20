<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Application\Auth\DTO\LoginInput;
use FoxPlatform\Api\Infrastructure\Http\Request;

class LoginRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'input' => new LoginInput(
                $this->requireEmail($data),
                $this->requireString($data, 'password', 'Senha', 6),
                $this->requireEnum($data, 'guard', ['admin', 'partner', 'driver'], 'Portal')
            ),
        ];
    }
}
