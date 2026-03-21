<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class SupportTicketCreateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'channel' => $this->requireString($data, 'channel', 'Canal', 2),
            'priority' => $this->requireEnum($data, 'priority', ['normal', 'high', 'critical'], 'Prioridade'),
            'subject' => $this->requireString($data, 'subject', 'Assunto', 3),
            'description' => $this->requireString($data, 'description', 'Descricao', 10),
        ];
    }
}
