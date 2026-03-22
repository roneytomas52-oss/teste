<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class AdminApprovalDecisionRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'decision' => $this->requireEnum(
                $data,
                'decision',
                ['approve', 'reject'],
                'Decisao da aprovacao'
            ),
            'note' => trim((string) ($data['note'] ?? '')),
        ];
    }
}
