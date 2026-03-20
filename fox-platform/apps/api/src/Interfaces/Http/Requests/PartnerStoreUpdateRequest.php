<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class PartnerStoreUpdateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'trade_name' => $this->requireString($data, 'trade_name', 'Nome de exibicao', 3),
            'legal_name' => $this->requireString($data, 'legal_name', 'Razao social', 3),
            'document_number' => $this->requireString($data, 'document_number', 'Documento', 8),
            'email' => $this->requireEmail($data),
            'phone' => $this->requireString($data, 'phone', 'Telefone', 8),
            'city' => $this->requireString($data, 'city', 'Cidade', 2),
            'state' => $this->requireString($data, 'state', 'Estado', 2),
            'country' => $this->requireString($data, 'country', 'Pais', 2),
            'description' => trim((string) ($data['description'] ?? '')),
        ];
    }
}
