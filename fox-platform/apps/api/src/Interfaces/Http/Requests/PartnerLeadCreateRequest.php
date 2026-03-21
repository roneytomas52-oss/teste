<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class PartnerLeadCreateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'company_name' => $this->requireString($data, 'company_name', 'Empresa', 2),
            'contact_name' => $this->requireString($data, 'contact_name', 'Responsavel', 2),
            'email' => $this->requireEmail($data),
            'phone' => $this->requireString($data, 'phone', 'Telefone', 8),
            'city' => $this->requireString($data, 'city', 'Cidade', 2),
            'business_type' => $this->requireEnum($data, 'business_type', ['restaurant', 'market', 'pharmacy', 'convenience'], 'Tipo de operacao'),
        ];
    }
}
