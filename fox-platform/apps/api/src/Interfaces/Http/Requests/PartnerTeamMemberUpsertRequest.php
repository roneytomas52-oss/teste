<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PartnerTeamMemberUpsertRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();
        $permissions = $data['permissions'] ?? [];

        if (!is_array($permissions) || $permissions === []) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => 'permissions',
                'message' => 'Permissoes devem ser informadas.',
            ]);
        }

        $normalizedPermissions = array_values(array_filter(array_map(
            static fn (mixed $value) => trim((string) $value),
            $permissions
        )));

        if ($normalizedPermissions === []) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => 'permissions',
                'message' => 'Permissoes devem ser informadas.',
            ]);
        }

        return [
            'full_name' => $this->requireString($data, 'full_name', 'Nome completo', 3),
            'email' => $this->requireEmail($data, 'email', 'E-mail'),
            'phone' => $this->requireString($data, 'phone', 'Telefone', 8),
            'role_slug' => $this->requireEnum($data, 'role_slug', ['manager', 'catalog', 'operations', 'finance', 'support'], 'Funcao'),
            'permissions' => $normalizedPermissions,
        ];
    }
}
