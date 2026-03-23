<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class CustomerRegisterRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'full_name' => $this->requireString($data, 'full_name', 'Nome completo', 3),
            'email' => $this->requireEmail($data),
            'phone' => $this->requireString($data, 'phone', 'Telefone', 8),
            'password' => $this->requireString($data, 'password', 'Senha', 6),
            'city' => trim((string) ($data['city'] ?? '')),
            'state' => trim((string) ($data['state'] ?? '')),
            'marketing_opt_in' => filter_var($data['marketing_opt_in'] ?? false, FILTER_VALIDATE_BOOL),
        ];
    }
}
