<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

abstract class FormRequest
{
    abstract public function validate(Request $request): array;

    protected function requireString(array $data, string $field, string $label, int $minLength = 1): string
    {
        $value = trim((string) ($data[$field] ?? ''));
        if ($value === '' || mb_strlen($value) < $minLength) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => $field,
                'message' => sprintf('%s e obrigatorio.', $label),
            ]);
        }

        return $value;
    }

    protected function requireEmail(array $data, string $field = 'email', string $label = 'Email'): string
    {
        $value = trim((string) ($data[$field] ?? ''));
        if ($value === '' || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => $field,
                'message' => sprintf('%s invalido.', $label),
            ]);
        }

        return $value;
    }

    protected function requireEnum(array $data, string $field, array $allowed, string $label): string
    {
        $value = trim((string) ($data[$field] ?? ''));
        if (!in_array($value, $allowed, true)) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => $field,
                'message' => sprintf('%s invalido.', $label),
            ]);
        }

        return $value;
    }
}
