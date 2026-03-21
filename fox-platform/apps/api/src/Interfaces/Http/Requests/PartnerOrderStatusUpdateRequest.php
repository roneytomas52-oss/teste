<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class PartnerOrderStatusUpdateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'status' => $this->requireEnum(
                $data,
                'status',
                ['pending_acceptance', 'accepted', 'preparing', 'ready_for_pickup', 'on_route', 'completed', 'cancelled'],
                'Status do pedido'
            ),
            'note' => trim((string) ($data['note'] ?? '')),
        ];
    }
}
