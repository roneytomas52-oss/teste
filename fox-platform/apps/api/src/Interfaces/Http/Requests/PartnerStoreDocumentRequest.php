<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class PartnerStoreDocumentRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'document_type' => $this->requireString($data, 'document_type', 'Tipo de documento', 2),
            'label' => $this->requireString($data, 'label', 'Rotulo do documento', 2),
            'file_name' => $this->requireString($data, 'file_name', 'Nome do arquivo', 2),
            'storage_path' => $this->requireString($data, 'storage_path', 'Caminho do arquivo', 2),
            'status' => $this->requireEnum($data, 'status', ['pending', 'approved', 'rejected'], 'Status'),
            'metadata' => is_array($data['metadata'] ?? null) ? $data['metadata'] : [],
        ];
    }
}
