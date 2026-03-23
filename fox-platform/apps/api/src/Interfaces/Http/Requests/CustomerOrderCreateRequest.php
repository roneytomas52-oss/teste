<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Interfaces\Http\Requests;

use FoxPlatform\Api\Infrastructure\Http\Request;

class CustomerOrderCreateRequest extends FormRequest
{
    public function validate(Request $request): array
    {
        $data = $request->body();
        $items = $data['items'] ?? null;

        if (!is_array($items) || $items === []) {
            throw new \FoxPlatform\Api\Infrastructure\Support\ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
                'field' => 'items',
                'message' => 'Ao menos um item e obrigatorio.',
            ]);
        }

        return [
            'store_id' => $this->requireString($data, 'store_id', 'Loja', 3),
            'customer_address' => $this->requireString($data, 'customer_address', 'Endereco', 5),
            'payment_method' => $this->requireEnum($data, 'payment_method', ['online_card', 'pix', 'cash'], 'Pagamento'),
            'items' => array_map(function (array $item): array {
                return [
                    'product_id' => trim((string) ($item['product_id'] ?? '')),
                    'quantity' => max(0, (int) ($item['quantity'] ?? 0)),
                    'notes' => trim((string) ($item['notes'] ?? '')),
                ];
            }, $items),
        ];
    }
}
