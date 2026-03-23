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
            'next_step' => 'A loja recebeu o pedido e vai iniciar a análise para aceite e preparo.',
        ];
    }

    public function getPublicOrderTracking(string $orderNumber): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                o.id,
                o.order_number,
                o.customer_name,
                o.customer_phone,
                o.customer_address,
                o.status,
                o.payment_method,
                o.payment_status,
                o.subtotal,
                o.delivery_fee,
                o.total,
                o.placed_at,
                o.accepted_at,
                o.completed_at,
                o.cancelled_at,
                s.trade_name,
                s.city,
                s.state,
                dp.modal AS driver_modal
             FROM orders o
             INNER JOIN stores s ON s.id = o.store_id
             LEFT JOIN driver_profiles dp ON dp.id = o.driver_profile_id
             WHERE o.order_number = :order_number
             LIMIT 1"
        );
        $statement->execute(['order_number' => $orderNumber]);
        $order = $statement->fetch();

        if (!$order) {
            throw new \FoxPlatform\Api\Infrastructure\Support\ApiException(404, 'ORDER_NOT_FOUND', 'Pedido nao encontrado.');
        }

        $itemsStatement = $this->pdo->prepare(
            "SELECT product_name, quantity, unit_price, total_price, notes
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY created_at ASC"
        );
        $itemsStatement->execute(['order_id' => $order['id']]);

        $timelineStatement = $this->pdo->prepare(
            "SELECT previous_status, next_status, note, created_at
             FROM order_status_logs
             WHERE order_id = :order_id
             ORDER BY created_at ASC"
        );
        $timelineStatement->execute(['order_id' => $order['id']]);

        return [
            'order' => [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'store_name' => $order['trade_name'],
                'store_region' => trim(($order['city'] ?: '-') . ' - ' . ($order['state'] ?: '-')),
                'customer_name' => $order['customer_name'],
                'customer_phone' => $order['customer_phone'] ?: '-',
                'customer_address' => $order['customer_address'] ?: '-',
                'status' => $this->mapPublicOrderStatusLabel((string) $order['status']),
                'status_key' => $order['status'],
                'payment_method' => $this->mapPaymentMethodLabel((string) $order['payment_method']),
                'payment_status' => $this->mapPaymentStatusLabel((string) $order['payment_status']),
                'subtotal' => $this->formatCurrency((float) ($order['subtotal'] ?? 0)),
                'delivery_fee' => $this->formatCurrency((float) ($order['delivery_fee'] ?? 0)),
                'total' => $this->formatCurrency((float) ($order['total'] ?? 0)),
                'placed_at' => $order['placed_at'],
                'accepted_at' => $order['accepted_at'],
                'completed_at' => $order['completed_at'],
                'cancelled_at' => $order['cancelled_at'],
                'driver_modal' => $order['driver_modal'] ?: '-',
                'progress_label' => $this->resolvePublicOrderProgress((string) $order['status']),
            ],
            'items' => array_map(
                fn (array $row) => [
                    'name' => $row['product_name'],
                    'quantity' => (int) $row['quantity'],
                    'unit_price' => $this->formatCurrency((float) ($row['unit_price'] ?? 0)),
                    'total_price' => $this->formatCurrency((float) ($row['total_price'] ?? 0)),
                    'notes' => $row['notes'] ?: '-',
                ],
                $itemsStatement->fetchAll() ?: []
            ),
            'timeline' => array_map(
                fn (array $row) => [
                    'title' => $this->mapPublicOrderStatusLabel((string) $row['next_status']),
                    'description' => $row['note'] ?: 'Atualizacao registrada na operacao da Fox Delivery.',
                    'created_at' => $row['created_at'],
                ],
                $timelineStatement->fetchAll() ?: []
            ),
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
            'next_step' => 'Nossa equipe comercial vai analisar o perfil da operação e retornar com os próximos passos.',
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
            'next_step' => 'O time operacional vai revisar seus dados e orientar a próxima etapa do cadastro.',
        ];
    }

    private function resolveCategoryDescription(string $slug, string $name): string
    {
        return match ($slug) {
            'restaurantes',
            'restaurant' => 'Refeições, lanches e pratos prontos com jornada de pedido objetiva.',
            'mercado',
            'market' => 'Compras do dia a dia com abastecimento mais prático para a rotina.',
            'farmacia',
            'pharmacy' => 'Itens de saúde, higiene e conveniência com acesso rápido pela plataforma.',
            'conveniencia',
            'convenience' => 'Produtos essenciais para reposição imediata e pedidos recorrentes.',
            default => sprintf('Operação de %s disponível na jornada pública da Fox Delivery.', strtolower($name)),
        };
    }

    private function resolveCategoryCta(string $slug): string
    {
        return match ($slug) {
            'restaurantes',
            'restaurant' => 'Explorar refeições',
            'mercado',
            'market' => 'Ver mercado',
            'farmacia',
            'pharmacy' => 'Ver farmácia',
            'conveniencia',
            'convenience' => 'Ver conveniência',
            default => 'Explorar categoria',
        };
    }

    private function formatCurrency(float $amount): string
    {
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }

    private function mapPublicOrderStatusLabel(string $status): string
    {
        return match ($status) {
            'pending_acceptance' => 'Aguardando aceite',
            'accepted' => 'Aceito pela loja',
            'preparing' => 'Em preparo',
            'ready_for_pickup' => 'Pronto para retirada',
            'on_route' => 'Em rota',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            default => 'Em processamento',
        };
    }

    private function mapPaymentMethodLabel(string $method): string
    {
        return match ($method) {
            'online_card' => 'Cartão online',
            'pix' => 'Pix',
            'cash' => 'Dinheiro',
            default => 'Não informado',
        };
    }

    private function mapPaymentStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'refunded' => 'Estornado',
            default => 'Não informado',
        };
    }

    private function resolvePublicOrderProgress(string $status): string
    {
        return match ($status) {
            'pending_acceptance' => 'A loja recebeu o pedido e está analisando o aceite.',
            'accepted' => 'Pedido aceito. A loja vai iniciar o preparo.',
            'preparing' => 'A loja está preparando o pedido.',
            'ready_for_pickup' => 'Pedido pronto para retirada pela operação.',
            'on_route' => 'Pedido saiu para entrega.',
            'completed' => 'Pedido concluído com sucesso.',
            'cancelled' => 'Pedido cancelado pela operação.',
            default => 'Pedido em processamento.'
        };
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
