<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PublicOrderCreateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();
        $items = $data['items'] ?? null;

        if (!is_array($items) || $items === []) {
            throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => 'items',
                'message' => 'Informe ao menos um item para criar o pedido.',
            ]);
        }

        $normalizedItems = [];
        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                    'field' => sprintf('items.%d', $index),
                    'message' => 'Item invalido na composicao do pedido.',
                ]);
            }

            $productId = trim((string) ($item['product_id'] ?? ''));
            $quantity = (int) ($item['quantity'] ?? 0);
            $notes = trim((string) ($item['notes'] ?? ''));

            if ($productId === '' || $quantity <= 0) {
                throw new ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                    'field' => sprintf('items.%d', $index),
                    'message' => 'Cada item precisa de produto e quantidade valida.',
                ]);
            }

            $normalizedItems[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'notes' => $notes,
            ];
        }

        return [
            'store_id' => $this->requireString($data, 'store_id', 'Loja', 10),
            'customer_name' => $this->requireString($data, 'customer_name', 'Nome do cliente', 3),
            'customer_phone' => $this->requireString($data, 'customer_phone', 'Telefone', 8),
            'customer_address' => $this->requireString($data, 'customer_address', 'Endereco', 5),
            'payment_method' => $this->requireEnum($data, 'payment_method', ['online_card', 'pix', 'cash'], 'Forma de pagamento'),
            'items' => $normalizedItems,
        ];
    }
}
