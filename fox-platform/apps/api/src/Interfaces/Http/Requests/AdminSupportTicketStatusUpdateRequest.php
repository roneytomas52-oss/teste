<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class AdminSupportTicketStatusUpdateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();
        $note = trim((string) ($data['note'] ?? ''));

        return [
            'status' => $this->requireEnum(
                $data,
                'status',
                ['open', 'in_progress', 'answered', 'resolved'],
                'Status do chamado'
            ),
            'note' => $note,
        ];
    }
}
