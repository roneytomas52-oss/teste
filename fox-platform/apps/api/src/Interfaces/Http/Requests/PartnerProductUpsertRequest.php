<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PartnerProductUpsertRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();

        $basePrice = filter_var($data['base_price'] ?? null, FILTER_VALIDATE_FLOAT);
        $stockQuantity = filter_var($data['stock_quantity'] ?? null, FILTER_VALIDATE_INT);
        $minStockQuantity = filter_var($data['min_stock_quantity'] ?? null, FILTER_VALIDATE_INT);

        if ($basePrice === false || $basePrice < 0) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Preco base invalido.', [
                'field' => 'base_price',
                'message' => 'Informe um preco base valido.',
            ]);
        }

        if ($stockQuantity === false || $stockQuantity < 0) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Estoque invalido.', [
                'field' => 'stock_quantity',
                'message' => 'Informe um estoque igual ou maior que zero.',
            ]);
        }

        if ($minStockQuantity === false || $minStockQuantity < 0) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Estoque minimo invalido.', [
                'field' => 'min_stock_quantity',
                'message' => 'Informe um estoque minimo igual ou maior que zero.',
            ]);
        }

        return [
            'category_id' => $this->requireString($data, 'category_id', 'Categoria'),
            'name' => $this->requireString($data, 'name', 'Nome do produto', 3),
            'description' => trim((string) ($data['description'] ?? '')),
            'sku' => strtoupper($this->requireString($data, 'sku', 'SKU', 3)),
            'base_price' => round((float) $basePrice, 2),
            'currency' => strtoupper(trim((string) ($data['currency'] ?? 'BRL')) ?: 'BRL'),
            'status' => $this->requireEnum($data, 'status', ['active', 'paused', 'draft'], 'Status do produto'),
            'stock_quantity' => (int) $stockQuantity,
            'min_stock_quantity' => (int) $minStockQuantity,
            'image_path' => trim((string) ($data['image_path'] ?? '')),
        ];
    }
}
