<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class ForgotPasswordRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        return [
            'email' => $this->requireEmail($request->body()),
        ];
    }
}
