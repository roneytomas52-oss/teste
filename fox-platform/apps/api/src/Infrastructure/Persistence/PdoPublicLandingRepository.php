<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Public\PublicLandingRepository;

class PdoPublicLandingRepository implements PublicLandingRepository
{
    use SupportsSqlDialect;

    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function getCategories(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                c.slug,
                c.name,
                SUM(CASE WHEN p.status = 'active' THEN 1 ELSE 0 END) AS active_products
             FROM categories c
             LEFT JOIN products p ON p.category_id = c.id
             WHERE c.status = 'active'
             GROUP BY c.id, c.slug, c.name
             ORDER BY active_products DESC, c.name ASC
             LIMIT 4"
        );

        return [
            'items' => array_map(
                fn (array $row) => [
                    'slug' => $row['slug'],
                    'name' => $row['name'],
                    'description' => $this->resolveCategoryDescription((string) $row['slug'], (string) $row['name']),
                    'cta' => $this->resolveCategoryCta((string) $row['slug']),
                    'product_count' => (int) ($row['active_products'] ?? 0),
                ],
                $statement->fetchAll() ?: []
            ),
        ];
    }

    public function getPlatformMetrics(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                (SELECT COUNT(DISTINCT city) FROM stores WHERE status = 'active') AS active_cities,
                (SELECT COUNT(*) FROM stores WHERE status = 'active') AS active_partners,
                (SELECT COUNT(*) FROM driver_profiles WHERE status = 'active') AS active_drivers,
                (SELECT COUNT(*) FROM orders WHERE status = 'completed') AS completed_orders"
        );
        $row = $statement->fetch() ?: [];

        return [
            'items' => [
                [
                    'label' => 'cidades com operacao ativa',
                    'value' => '+' . (string) ((int) ($row['active_cities'] ?? 0)),
                ],
                [
                    'label' => 'parceiros ativos na plataforma',
                    'value' => '+' . number_format((int) ($row['active_partners'] ?? 0), 0, ',', '.'),
                ],
                [
                    'label' => 'entregadores habilitados',
                    'value' => '+' . number_format((int) ($row['active_drivers'] ?? 0), 0, ',', '.'),
                ],
                [
                    'label' => 'pedidos concluidos no ecossistema',
                    'value' => '+' . number_format((int) ($row['completed_orders'] ?? 0), 0, ',', '.'),
                ],
            ],
        ];
    }

    public function getStores(array $filters = []): array
    {
        $conditions = ["s.status = 'active'"];
        $bindings = [];

        $city = trim((string) ($filters['city'] ?? ''));
        $category = trim((string) ($filters['category'] ?? ''));
        $search = trim((string) ($filters['search'] ?? ''));

        if ($city !== '') {
            $conditions[] = 'LOWER(s.city) = LOWER(:city)';
            $bindings['city'] = $city;
        }

        if ($category !== '') {
            $conditions[] = 'LOWER(COALESCE(c.slug, \'\')) = LOWER(:category)';
            $bindings['category'] = $category;
        }

        if ($search !== '') {
            $conditions[] = '(LOWER(s.trade_name) LIKE LOWER(:search) OR LOWER(COALESCE(s.city, \'\')) LIKE LOWER(:search))';
            $bindings['search'] = '%' . $search . '%';
        }

        $where = implode(' AND ', $conditions);
        $statement = $this->pdo->prepare(
            "SELECT
                s.id,
                s.trade_name,
                s.city,
                s.state,
                COUNT(DISTINCT CASE WHEN p.status = 'active' THEN p.id END) AS active_products,
                COUNT(DISTINCT CASE WHEN o.status = 'completed' THEN o.id END) AS completed_orders,
                MIN(c.name) AS primary_category
             FROM stores s
             LEFT JOIN products p ON p.store_id = s.id
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN orders o ON o.store_id = s.id
             WHERE {$where}
             GROUP BY s.id, s.trade_name, s.city, s.state
             ORDER BY completed_orders DESC, active_products DESC, s.trade_name ASC"
        );
        $statement->execute($bindings);

        return [
            'filters' => [
                'city' => $city,
                'category' => $category,
                'search' => $search,
            ],
            'items' => array_map(
                fn (array $row) => [
                    'id' => $row['id'],
                    'trade_name' => $row['trade_name'],
                    'city' => $row['city'] ?: '-',
                    'state' => $row['state'] ?: '-',
                    'primary_category' => $row['primary_category'] ?: 'Multicategoria',
                    'product_count' => (int) ($row['active_products'] ?? 0),
                    'completed_orders' => (int) ($row['completed_orders'] ?? 0),
                    'lead' => sprintf(
                        '%s em %s com %d itens ativos e operacao pronta para pedido.',
                        $row['trade_name'],
                        $row['city'] ?: 'sua cidade',
                        (int) ($row['active_products'] ?? 0)
                    ),
                ],
                $statement->fetchAll() ?: []
            ),
        ];
    }

    public function getStoreDetail(string $storeId): array
    {
        $storeStatement = $this->pdo->prepare(
            "SELECT
                s.id,
                s.trade_name,
                s.legal_name,
                s.city,
                s.state,
                s.phone,
                s.email,
                COUNT(DISTINCT CASE WHEN p.status = 'active' THEN p.id END) AS active_products,
                COUNT(DISTINCT CASE WHEN o.status = 'completed' THEN o.id END) AS completed_orders
             FROM stores s
             LEFT JOIN products p ON p.store_id = s.id
             LEFT JOIN orders o ON o.store_id = s.id
             WHERE s.id = :store_id AND s.status = 'active'
             GROUP BY s.id, s.trade_name, s.legal_name, s.city, s.state, s.phone, s.email"
        );
        $storeStatement->execute(['store_id' => $storeId]);
        $store = $storeStatement->fetch();

        if (!$store) {
            throw $this->notFoundStore();
        }

        $productStatement = $this->pdo->prepare(
            "SELECT
                p.id,
                p.name,
                p.description,
                p.base_price,
                p.stock_quantity,
                c.name AS category_name,
                c.slug AS category_slug
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.store_id = :store_id
               AND p.status = 'active'
               AND p.stock_quantity > 0
             ORDER BY c.name ASC, p.name ASC"
        );
        $productStatement->execute(['store_id' => $storeId]);
        $products = $productStatement->fetchAll() ?: [];

        return [
            'store' => [
                'id' => $store['id'],
                'trade_name' => $store['trade_name'],
                'legal_name' => $store['legal_name'],
                'city' => $store['city'] ?: '-',
                'state' => $store['state'] ?: '-',
                'phone' => $store['phone'] ?: '-',
                'email' => $store['email'] ?: '-',
                'product_count' => (int) ($store['active_products'] ?? 0),
                'completed_orders' => (int) ($store['completed_orders'] ?? 0),
                'lead' => sprintf(
                    '%s opera em %s com catalogo ativo pronto para pedido.',
                    $store['trade_name'],
                    $store['city'] ?: 'sua cidade'
                ),
            ],
            'products' => array_map(
                fn (array $row) => [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'] ?: 'Produto ativo no catalogo publico da Fox Delivery.',
                    'price' => $this->formatCurrency((float) ($row['base_price'] ?? 0)),
                    'price_value' => (float) ($row['base_price'] ?? 0),
                    'stock_quantity' => (int) ($row['stock_quantity'] ?? 0),
                    'category_name' => $row['category_name'] ?: 'Categoria geral',
                    'category_slug' => $row['category_slug'] ?: 'general',
                ],
                $products
            ),
        ];
    }

    public function createPublicOrder(array $data): array
    {
        $storeId = (string) $data['store_id'];
        $storeDetail = $this->getStoreDetail($storeId);
        $availableProducts = [];
        foreach ($storeDetail['products'] as $product) {
            $availableProducts[$product['id']] = $product;
        }

        $subtotal = 0.0;
        $lineItems = [];

        foreach ($data['items'] as $item) {
            $product = $availableProducts[$item['product_id']] ?? null;
            if ($product === null) {
                throw $this->validationError('items', 'Existe produto invalido para esta loja.');
            }

            if ((int) $item['quantity'] > (int) $product['stock_quantity']) {
                throw $this->validationError('items', sprintf('Estoque insuficiente para %s.', $product['name']));
            }

            $unitPrice = (float) $product['price_value'];
            $totalPrice = $unitPrice * (int) $item['quantity'];
            $subtotal += $totalPrice;

            $lineItems[] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'notes' => (string) ($item['notes'] ?? ''),
            ];
        }

        $deliveryFee = 7.90;
        $total = $subtotal + $deliveryFee;
        $orderId = $this->newUuid();
        $orderNumber = 'FD-' . strtoupper(substr(str_replace('-', '', $orderId), 0, 8));
        $paymentMethod = (string) $data['payment_method'];
        $paymentStatus = $paymentMethod === 'online_card' ? 'paid' : 'pending';

        try {
            $this->pdo->beginTransaction();

            $orderStatement = $this->pdo->prepare(
                "INSERT INTO orders (
                    id, store_id, order_number, customer_name, customer_phone, customer_address,
                    status, payment_method, payment_status, subtotal, delivery_fee, total
                 ) VALUES (
                    :id, :store_id, :order_number, :customer_name, :customer_phone, :customer_address,
                    'pending_acceptance', :payment_method, :payment_status, :subtotal, :delivery_fee, :total
                 )"
            );
            $orderStatement->execute([
                'id' => $orderId,
                'store_id' => $storeId,
                'order_number' => $orderNumber,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
            ]);

            $itemStatement = $this->pdo->prepare(
                "INSERT INTO order_items (
                    id, order_id, product_id, product_name, quantity, unit_price, total_price, notes
                 ) VALUES (
                    :id, :order_id, :product_id, :product_name, :quantity, :unit_price, :total_price, :notes
                 )"
            );

            $stockStatement = $this->pdo->prepare(
                "UPDATE products
                 SET stock_quantity = stock_quantity - :decrement_quantity,
                     sold_count = sold_count + :sold_quantity,
                     updated_at = NOW()
                 WHERE id = :product_id"
            );

            foreach ($lineItems as $lineItem) {
                $itemStatement->execute([
                    'id' => $this->newUuid(),
                    'order_id' => $orderId,
                    'product_id' => $lineItem['product_id'],
                    'product_name' => $lineItem['product_name'],
                    'quantity' => $lineItem['quantity'],
                    'unit_price' => $lineItem['unit_price'],
                    'total_price' => $lineItem['total_price'],
                    'notes' => $lineItem['notes'] ?: null,
                ]);

                $stockStatement->execute([
                    'product_id' => $lineItem['product_id'],
                    'decrement_quantity' => $lineItem['quantity'],
                    'sold_quantity' => $lineItem['quantity'],
                ]);
            }

            $statusStatement = $this->pdo->prepare(
                "INSERT INTO order_status_logs (
                    id, order_id, previous_status, next_status, actor_user_id, note
                 ) VALUES (
                    :id, :order_id, NULL, 'pending_acceptance', NULL, :note
                 )"
            );
            $statusStatement->execute([
                'id' => $this->newUuid(),
                'order_id' => $orderId,
                'note' => 'Pedido criado pela jornada publica da Fox Delivery.',
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        return [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'store_name' => $storeDetail['store']['trade_name'],
            'status' => 'recebido',
            'status_key' => 'pending_acceptance',
            'total' => $this->formatCurrency($total),
            'next_step' => 'A loja recebeu o pedido e vai iniciar a analise para aceite e preparo.',
        ];
    }

    public function createPartnerLead(array $data): array
    {
        $leadId = $this->newUuid();
        $statement = $this->pdo->prepare(
            "INSERT INTO partner_leads (
                id, company_name, contact_name, email, phone, city, business_type, status, source
             ) VALUES (
                :id, :company_name, :contact_name, :email, :phone, :city, :business_type, 'new', 'landing'
             )"
        );
        $statement->execute([
            'id' => $leadId,
            'company_name' => $data['company_name'],
            'contact_name' => $data['contact_name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'city' => $data['city'],
            'business_type' => $data['business_type'],
        ]);

        return [
            'protocol' => 'PAR-' . strtoupper(substr(str_replace('-', '', $leadId), 0, 8)),
            'status' => 'recebido',
            'next_step' => 'Nossa equipe comercial vai analisar o perfil da operacao e retornar com os proximos passos.',
        ];
    }

    public function createDriverLead(array $data): array
    {
        $leadId = $this->newUuid();
        $statement = $this->pdo->prepare(
            "INSERT INTO driver_leads (
                id, full_name, email, phone, city, modal, status, source
             ) VALUES (
                :id, :full_name, :email, :phone, :city, :modal, 'new', 'landing'
             )"
        );
        $statement->execute([
            'id' => $leadId,
            'full_name' => $data['full_name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'city' => $data['city'],
            'modal' => $data['modal'],
        ]);

        return [
            'protocol' => 'DRV-' . strtoupper(substr(str_replace('-', '', $leadId), 0, 8)),
            'status' => 'recebido',
            'next_step' => 'O time operacional vai revisar seus dados e orientar a proxima etapa do cadastro.',
        ];
    }

    private function resolveCategoryDescription(string $slug, string $name): string
    {
        return match ($slug) {
            'restaurantes',
            'restaurant' => 'Refeicoes, lanches e pratos prontos com jornada de pedido objetiva.',
            'mercado',
            'market' => 'Compras do dia a dia com abastecimento mais pratico para a rotina.',
            'farmacia',
            'pharmacy' => 'Itens de saude, higiene e conveniencia com acesso rapido pela plataforma.',
            'conveniencia',
            'convenience' => 'Produtos essenciais para reposicao imediata e pedidos recorrentes.',
            default => sprintf('Operacao de %s disponivel na jornada publica da Fox Delivery.', strtolower($name)),
        };
    }

    private function resolveCategoryCta(string $slug): string
    {
        return match ($slug) {
            'restaurantes',
            'restaurant' => 'Explorar refeicoes',
            'mercado',
            'market' => 'Ver mercado',
            'farmacia',
            'pharmacy' => 'Ver farmacia',
            'conveniencia',
            'convenience' => 'Ver conveniencia',
            default => 'Explorar categoria',
        };
    }

    private function formatCurrency(float $amount): string
    {
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }

    private function notFoundStore(): \FoxPlatform\Api\Infrastructure\Support\ApiException
    {
        return new \FoxPlatform\Api\Infrastructure\Support\ApiException(404, 'STORE_NOT_FOUND', 'Loja nao encontrada.');
    }

    private function validationError(string $field, string $message): \FoxPlatform\Api\Infrastructure\Support\ApiException
    {
        return new \FoxPlatform\Api\Infrastructure\Support\ApiException(422, 'VALIDATION_ERROR', 'Dados invalidos.', [
            'field' => $field,
            'message' => $message,
        ]);
    }
}
