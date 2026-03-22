<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class AdminAccessMemberStatusRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'status' => $this->requireEnum($data, 'status', ['pending', 'active', 'suspended', 'blocked'], 'Status'),
        ];
    }
}
