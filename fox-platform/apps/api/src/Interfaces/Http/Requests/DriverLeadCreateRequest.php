<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class DriverLeadCreateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'full_name' => $this->requireString($data, 'full_name', 'Nome completo', 3),
            'email' => $this->requireEmail($data),
            'phone' => $this->requireString($data, 'phone', 'Telefone', 8),
            'city' => $this->requireString($data, 'city', 'Cidade', 2),
            'modal' => $this->requireEnum($data, 'modal', ['bike', 'motorcycle', 'car'], 'Modalidade'),
        ];
    }
}
