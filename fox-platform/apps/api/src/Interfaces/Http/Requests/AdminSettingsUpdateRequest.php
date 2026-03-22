<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class AdminSettingsUpdateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();
        $branding = (array) ($data['branding'] ?? []);
        $operations = (array) ($data['operations'] ?? []);
        $notifications = (array) ($data['notifications'] ?? []);
        $security = (array) ($data['security'] ?? []);

        $payload = [
            'branding' => [
                'platform_name' => $this->requireString($branding, 'platform_name', 'Nome da plataforma', 3),
                'support_email' => $this->requireEmail($branding, 'support_email', 'E-mail de suporte'),
                'partner_login_url' => $this->requireUrlOrFallback($branding, 'partner_login_url', 'URL do login do parceiro'),
                'support_phone' => $this->optionalString($branding, 'support_phone'),
            ],
            'operations' => [
                'default_order_sla_minutes' => $this->requirePositiveInt($operations, 'default_order_sla_minutes', 'SLA padrao dos pedidos'),
                'partner_review_window_hours' => $this->requirePositiveIntOrFallback(
                    $operations,
                    'partner_review_window_hours',
                    'partner_auto_approval',
                    'Janela de revisao de parceiros',
                    24
                ),
                'driver_review_window_hours' => $this->requirePositiveIntOrFallback(
                    $operations,
                    'driver_review_window_hours',
                    'driver_auto_approval',
                    'Janela de revisao de entregadores',
                    24
                ),
                'partner_auto_approval' => $this->optionalBool($operations, 'partner_auto_approval', false),
                'driver_auto_approval' => $this->optionalBool($operations, 'driver_auto_approval', false),
            ],
            'notifications' => [
                'partner_polling_seconds' => $this->requirePositiveIntOrFallback(
                    $notifications,
                    'partner_polling_seconds',
                    'refresh_interval_seconds',
                    'Polling do parceiro',
                    30
                ),
                'driver_polling_seconds' => $this->requirePositiveIntOrFallback(
                    $notifications,
                    'driver_polling_seconds',
                    'refresh_interval_seconds',
                    'Polling do entregador',
                    30
                ),
                'admin_digest_enabled' => $this->optionalBool($notifications, 'admin_digest_enabled', false),
                'partner_digest_enabled' => $this->optionalBool($notifications, 'partner_digest_enabled', true),
                'driver_digest_enabled' => $this->optionalBool($notifications, 'driver_digest_enabled', true),
            ],
            'security' => [
                'access_token_ttl_minutes' => $this->requirePositiveIntOrFallback(
                    $security,
                    'access_token_ttl_minutes',
                    'access_token_ttl_seconds',
                    'Validade do access token',
                    15,
                    60
                ),
                'refresh_token_ttl_days' => $this->requirePositiveIntOrFallback(
                    $security,
                    'refresh_token_ttl_days',
                    'refresh_token_ttl_seconds',
                    'Validade do refresh token',
                    30,
                    86400
                ),
                'password_reset_token_ttl_minutes' => $this->requirePositiveIntOrFallback(
                    $security,
                    'password_reset_token_ttl_minutes',
                    'password_reset_ttl_seconds',
                    'Validade da redefinicao de senha',
                    60,
                    60
                ),
            ],
        ];

        return ['settings' => $payload];
    }

    private function requirePositiveInt(array $data, string $field, string $label): int
    {
        $value = (int) ($data[$field] ?? 0);
        if ($value <= 0) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => $field,
                'message' => sprintf('%s deve ser maior que zero.', $label),
            ]);
        }

        return $value;
    }

    private function requirePositiveIntOrFallback(
        array $data,
        string $field,
        string $fallbackField,
        string $label,
        int $defaultValue,
        int $fallbackDivisor = 1
    ): int {
        if (array_key_exists($field, $data)) {
            return $this->requirePositiveInt($data, $field, $label);
        }

        if (array_key_exists($fallbackField, $data)) {
            $fallback = $this->requirePositiveInt($data, $fallbackField, $label);
            return max(1, (int) ceil($fallback / $fallbackDivisor));
        }

        return $defaultValue;
    }

    private function requireBool(array $data, string $field, string $label): bool
    {
        if (!array_key_exists($field, $data)) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => $field,
                'message' => sprintf('%s e obrigatorio.', $label),
            ]);
        }

        return filter_var($data[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }

    private function optionalBool(array $data, string $field, bool $default): bool
    {
        if (!array_key_exists($field, $data)) {
            return $default;
        }

        return filter_var($data[$field], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    private function optionalString(array $data, string $field): ?string
    {
        $value = trim((string) ($data[$field] ?? ''));
        return $value !== '' ? $value : null;
    }

    private function requireUrlOrFallback(array $data, string $field, string $label): string
    {
        $value = trim((string) ($data[$field] ?? ''));
        if ($value === '') {
            return 'https://foxgodelivery.com.br/login/parceiro';
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => $field,
                'message' => sprintf('%s deve ser uma URL valida.', $label),
            ]);
        }

        return $value;
    }
}
