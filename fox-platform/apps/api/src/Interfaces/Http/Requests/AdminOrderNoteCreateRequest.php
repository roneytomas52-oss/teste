<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class AdminOrderNoteCreateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'note' => $this->requireString($data, 'note', 'Observacao', 3),
        ];
    }
}
