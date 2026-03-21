<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class PartnerTeamMemberStatusRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        return [
            'status' => $this->requireEnum($data, 'status', ['invited', 'active', 'suspended'], 'Status'),
        ];
    }
}
