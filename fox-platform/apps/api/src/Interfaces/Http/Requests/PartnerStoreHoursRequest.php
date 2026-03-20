<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PartnerStoreHoursRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $hours = $request->body()['hours'] ?? null;
        if (!is_array($hours) || $hours === []) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Lista de horarios invalida.', [
                'field' => 'hours',
                'message' => 'Envie ao menos um horario.',
            ]);
        }

        $validated = [];
        foreach ($hours as $index => $hour) {
            if (!is_array($hour)) {
                throw new ApiException(422, 'VALIDATION_ERROR', 'Horario invalido.', [
                    'field' => "hours.$index",
                    'message' => 'Cada horario deve ser um objeto.',
                ]);
            }

            $weekday = (int) ($hour['weekday'] ?? -1);
            if ($weekday < 0 || $weekday > 6) {
                throw new ApiException(422, 'VALIDATION_ERROR', 'Dia da semana invalido.', [
                    'field' => "hours.$index.weekday",
                    'message' => 'weekday deve estar entre 0 e 6.',
                ]);
            }

            $opensAt = trim((string) ($hour['opens_at'] ?? ''));
            $closesAt = trim((string) ($hour['closes_at'] ?? ''));
            if (!preg_match('/^\d{2}:\d{2}$/', $opensAt) || !preg_match('/^\d{2}:\d{2}$/', $closesAt)) {
                throw new ApiException(422, 'VALIDATION_ERROR', 'Horario invalido.', [
                    'field' => "hours.$index",
                    'message' => 'Utilize o formato HH:MM.',
                ]);
            }

            $validated[] = [
                'weekday' => $weekday,
                'opens_at' => $opensAt,
                'closes_at' => $closesAt,
                'is_active' => filter_var($hour['is_active'] ?? true, FILTER_VALIDATE_BOOL),
            ];
        }

        return ['hours' => $validated];
    }
}
