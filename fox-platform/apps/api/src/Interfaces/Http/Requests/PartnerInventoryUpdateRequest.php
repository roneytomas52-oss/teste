<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PartnerInventoryUpdateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        $stockQuantity = filter_var($data['stock_quantity'] ?? null, FILTER_VALIDATE_INT);
        $minStockQuantity = filter_var($data['min_stock_quantity'] ?? null, FILTER_VALIDATE_INT);

        if ($stockQuantity === false || $stockQuantity < 0) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Estoque atual invalido.', [
                'field' => 'stock_quantity',
                'message' => 'Informe um estoque atual igual ou maior que zero.',
            ]);
        }

        if ($minStockQuantity === false || $minStockQuantity < 0) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Estoque minimo invalido.', [
                'field' => 'min_stock_quantity',
                'message' => 'Informe um estoque minimo igual ou maior que zero.',
            ]);
        }

        return [
            'stock_quantity' => (int) $stockQuantity,
            'min_stock_quantity' => (int) $minStockQuantity,
            'status' => $this->requireEnum($data, 'status', ['active', 'paused', 'draft'], 'Status do produto'),
            'note' => trim((string) ($data['note'] ?? '')),
        ];
    }
}
