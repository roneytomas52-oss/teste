<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class ResetPasswordRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'email' => $this->requireEmail($data),
            'token' => $this->requireString($data, 'token', 'Token', 8),
            'password' => $this->requireString($data, 'password', 'Senha', 6),
        ];
    }
}
