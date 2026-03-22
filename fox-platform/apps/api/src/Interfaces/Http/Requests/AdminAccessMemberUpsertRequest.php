<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class AdminAccessMemberUpsertRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'full_name' => $this->requireString($data, 'full_name', 'Nome completo', 3),
            'email' => $this->requireEmail($data, 'email', 'E-mail'),
            'phone' => $this->requireString($data, 'phone', 'Telefone', 8),
            'department' => $this->optionalString($data, 'department'),
            'role_slug' => $this->requireEnum(
                $data,
                'role_slug',
                ['super_admin', 'admin_operacional', 'admin_financeiro', 'admin_comercial', 'suporte'],
                'Perfil administrativo'
            ),
            'status' => $this->requireEnum($data, 'status', ['pending', 'active', 'suspended', 'blocked'], 'Status'),
        ];
    }

    private function optionalString(array $data, string $field): ?string
    {
        $value = trim((string) ($data[$field] ?? ''));
        return $value !== '' ? $value : null;
    }
}
