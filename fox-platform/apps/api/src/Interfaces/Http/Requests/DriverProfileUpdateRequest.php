<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class DriverProfileUpdateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'full_name' => $this->requireString($data, 'full_name', 'Nome completo', 3),
            'email' => $this->requireEmail($data),
            'phone' => $this->requireString($data, 'phone', 'Telefone', 8),
            'modal' => $this->requireString($data, 'modal', 'Modalidade', 3),
            'city' => $this->requireString($data, 'city', 'Cidade', 2),
            'bank_name' => $this->requireString($data, 'bank_name', 'Banco', 3),
            'bank_branch_number' => $this->requireString($data, 'bank_branch_number', 'Agencia', 2),
            'bank_account_number' => $this->requireString($data, 'bank_account_number', 'Conta', 2),
        ];
    }
}
