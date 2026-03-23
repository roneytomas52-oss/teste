<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Customer\CustomerPortalRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoCustomerPortalRepository implements CustomerPortalRepository
{
    use SupportsSqlDialect;

    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function registerCustomer(array $data): array
    {
        $existing = $this->pdo->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(:email) LIMIT 1');
        $existing->execute(['email' => $data['email']]);
        if ($existing->fetchColumn()) {
            throw new ApiException(409, 'CUSTOMER_EMAIL_ALREADY_EXISTS', 'Ja existe uma conta de cliente com este e-mail.');
        }

        $userId = $this->newUuid();
        $profileId = $this->newUuid();

        $this->pdo->beginTransaction();
        try {
            $userInsert = $this->pdo->prepare(
                "INSERT INTO users (
                    id, full_name, email, phone, password_hash, status, locale
                 ) VALUES (
                    :id, :full_name, :email, :phone, :password_hash, 'active', 'pt_BR'
                 )"
            );
            $userInsert->execute([
                'id' => $userId,
                'full_name' => $data['full_name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'],
                'password_hash' => $data['password_hash'],
            ]);

            $profileInsert = $this->pdo->prepare(
                "INSERT INTO customer_profiles (
                    id, user_id, city, state, marketing_opt_in
                 ) VALUES (
                    :id, :user_id, :city, :state, :marketing_opt_in
                 )"
            );
            $profileInsert->execute([
                'id' => $profileId,
                'user_id' => $userId,
                'city' => $data['city'] ?: null,
                'state' => $data['state'] ?: null,
                'marketing_opt_in' => $data['marketing_opt_in'] ? 1 : 0,
            ]);

            $roleStatement = $this->pdo->prepare("SELECT id FROM roles WHERE slug = 'customer' LIMIT 1");
            $roleStatement->execute();
            $roleId = $roleStatement->fetchColumn();
            if (!$roleId) {
                throw new ApiException(500, 'CUSTOMER_ROLE_NOT_FOUND', 'Role de cliente nao encontrada.');
            }

            $roleInsert = $this->pdo->prepare(
                "INSERT INTO user_roles (id, user_id, role_id)
                 VALUES (:id, :user_id, :role_id)"
            );
            $roleInsert->execute([
                'id' => $this->newUuid(),
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        return [
            'user_id' => $userId,
            'full_name' => $data['full_name'],
            'email' => strtolower($data['email']),
            'status' => 'active',
            'next_step' => 'Cadastro concluido. Agora voce pode entrar e acompanhar seus pedidos.'
        ];
    }

    public function getProfile(string $userId): array
    {
        return $this->resolveCustomer($userId);
    }

    public function updateProfile(string $userId, array $data): array
    {
        $this->resolveCustomer($userId);

        $this->pdo->beginTransaction();
        try {
            $userUpdate = $this->pdo->prepare(
                "UPDATE users
                 SET full_name = :full_name,
                     email = :email,
                     phone = :phone,
                     updated_at = NOW()
                 WHERE id = :user_id"
            );
            $userUpdate->execute([
                'full_name' => $data['full_name'],
                'email' => strtolower($data['email']),
                'phone' => $data['phone'],
                'user_id' => $userId,
            ]);

            $profileUpdate = $this->pdo->prepare(
                "UPDATE customer_profiles
                 SET city = :city,
                     state = :state,
                     marketing_opt_in = :marketing_opt_in,
                     updated_at = NOW()
                 WHERE user_id = :user_id"
            );
            $profileUpdate->execute([
                'city' => $data['city'] ?: null,
                'state' => $data['state'] ?: null,
                'marketing_opt_in' => $data['marketing_opt_in'] ? 1 : 0,
                'user_id' => $userId,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        return $this->getProfile($userId);
    }

    public function getOrders(string $userId): array
    {
        $customer = $this->resolveCustomer($userId);

        $statement = $this->pdo->prepare(
            "SELECT
                o.id,
                o.order_number,
                o.status,
                o.payment_method,
                o.payment_status,
                o.total,
                o.placed_at,
                s.trade_name
             FROM orders o
             INNER JOIN stores s ON s.id = o.store_id
             WHERE o.customer_user_id = :user_id
             ORDER BY o.placed_at DESC"
        );
        $statement->execute(['user_id' => $userId]);

        $items = array_map(
            fn (array $row) => [
                'order_id' => $row['id'],
                'order_number' => $row['order_number'],
                'store_name' => $row['trade_name'],
                'status' => $this->mapOrderStatusLabel((string) $row['status']),
                'status_key' => $row['status'],
                'payment_method' => $this->mapPaymentMethodLabel((string) $row['payment_method']),
                'payment_status' => $this->mapPaymentStatusLabel((string) $row['payment_status']),
                'total' => $this->formatCurrency((float) ($row['total'] ?? 0)),
                'placed_at' => $row['placed_at'],
            ],
            $statement->fetchAll() ?: []
        );

        return [
            'customer' => [
                'id' => $customer['user_id'],
                'full_name' => $customer['full_name'],
            ],
            'summary' => [
                ['label' => 'pedidos no historico', 'value' => (string) count($items)],
                ['label' => 'pedidos concluidos', 'value' => (string) count(array_filter($items, static fn (array $item) => $item['status_key'] === 'completed'))],
                ['label' => 'pedidos ativos', 'value' => (string) count(array_filter($items, static fn (array $item) => in_array($item['status_key'], ['pending_acceptance', 'accepted', 'preparing', 'ready_for_pickup', 'on_route'], true)))],
            ],
            'items' => $items,
        ];
    }

    public function getOrderDetail(string $userId, string $orderId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                o.id,
                o.order_number,
                o.customer_name,
                o.customer_phone,
                o.customer_address,
                o.customer_email,
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
                s.state
             FROM orders o
             INNER JOIN stores s ON s.id = o.store_id
             WHERE o.id = :order_id
               AND o.customer_user_id = :user_id
             LIMIT 1"
        );
        $statement->execute([
            'order_id' => $orderId,
            'user_id' => $userId,
        ]);
        $order = $statement->fetch();

        if (!$order) {
            throw new ApiException(404, 'CUSTOMER_ORDER_NOT_FOUND', 'Pedido nao encontrado para esta conta.');
        }

        $itemsStatement = $this->pdo->prepare(
            "SELECT product_name, quantity, unit_price, total_price, notes
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY created_at ASC"
        );
        $itemsStatement->execute(['order_id' => $orderId]);

        $timelineStatement = $this->pdo->prepare(
            "SELECT previous_status, next_status, note, created_at
             FROM order_status_logs
             WHERE order_id = :order_id
             ORDER BY created_at ASC"
        );
        $timelineStatement->execute(['order_id' => $orderId]);

        return [
            'order' => [
                'id' => $order['id'],
                'order_number' => $order['order_number'],
                'store_name' => $order['trade_name'],
                'store_region' => trim(($order['city'] ?: '-') . ' - ' . ($order['state'] ?: '-')),
                'customer_name' => $order['customer_name'],
                'customer_phone' => $order['customer_phone'] ?: '-',
                'customer_address' => $order['customer_address'] ?: '-',
                'customer_email' => $order['customer_email'] ?: '-',
                'status' => $this->mapOrderStatusLabel((string) $order['status']),
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
                'progress_label' => $this->resolveProgress((string) $order['status']),
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
                    'title' => $this->mapOrderStatusLabel((string) $row['next_status']),
                    'description' => $row['note'] ?: 'Atualizacao registrada na operacao da Fox Delivery.',
                    'created_at' => $row['created_at'],
                ],
                $timelineStatement->fetchAll() ?: []
            ),
        ];
    }

    public function createOrder(string $userId, array $data): array
    {
        $customer = $this->resolveCustomer($userId);
        $storeDetail = $this->loadStoreDetail((string) $data['store_id']);
        $availableProducts = [];
        foreach ($storeDetail['products'] as $product) {
            $availableProducts[$product['id']] = $product;
        }

        $subtotal = 0.0;
        $lineItems = [];
        foreach ($data['items'] as $item) {
            $product = $availableProducts[$item['product_id']] ?? null;
            if ($product === null) {
                throw new ApiException(422, 'VALIDATION_ERROR', 'Existe produto invalido para esta loja.');
            }

            if ((int) $item['quantity'] > (int) $product['stock_quantity']) {
                throw new ApiException(422, 'VALIDATION_ERROR', sprintf('Estoque insuficiente para %s.', $product['name']));
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

        $this->pdo->beginTransaction();
        try {
            $orderStatement = $this->pdo->prepare(
                "INSERT INTO orders (
                    id, store_id, customer_user_id, order_number, customer_name, customer_phone, customer_address, customer_email,
                    status, payment_method, payment_status, subtotal, delivery_fee, total
                 ) VALUES (
                    :id, :store_id, :customer_user_id, :order_number, :customer_name, :customer_phone, :customer_address, :customer_email,
                    'pending_acceptance', :payment_method, :payment_status, :subtotal, :delivery_fee, :total
                 )"
            );
            $orderStatement->execute([
                'id' => $orderId,
                'store_id' => $data['store_id'],
                'customer_user_id' => $userId,
                'order_number' => $orderNumber,
                'customer_name' => $customer['full_name'],
                'customer_phone' => $customer['phone'],
                'customer_address' => $data['customer_address'],
                'customer_email' => $customer['email'],
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
                    :id, :order_id, NULL, 'pending_acceptance', :actor_user_id, :note
                 )"
            );
            $statusStatement->execute([
                'id' => $this->newUuid(),
                'order_id' => $orderId,
                'actor_user_id' => $userId,
                'note' => 'Pedido criado pela conta autenticada do cliente.',
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
            'next_step' => 'Pedido criado com sua conta. A loja recebeu e vai iniciar a análise para aceite e preparo.'
        ];
    }

    private function resolveCustomer(string $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                u.id AS user_id,
                u.full_name,
                u.email,
                u.phone,
                cp.city,
                cp.state,
                cp.marketing_opt_in
             FROM users u
             INNER JOIN customer_profiles cp ON cp.user_id = u.id
             WHERE u.id = :user_id
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);
        $row = $statement->fetch();

        if (!$row) {
            throw new ApiException(404, 'CUSTOMER_NOT_FOUND', 'Perfil do cliente nao encontrado.');
        }

        return [
            'user_id' => $row['user_id'],
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?: '',
            'city' => $row['city'] ?: '',
            'state' => $row['state'] ?: '',
            'marketing_opt_in' => filter_var($row['marketing_opt_in'], FILTER_VALIDATE_BOOL),
        ];
    }

    private function loadStoreDetail(string $storeId): array
    {
        $storeStatement = $this->pdo->prepare(
            "SELECT
                s.id,
                s.trade_name,
                s.city,
                s.state,
                COUNT(DISTINCT CASE WHEN p.status = 'active' THEN p.id END) AS active_products,
                COUNT(DISTINCT CASE WHEN o.status = 'completed' THEN o.id END) AS completed_orders
             FROM stores s
             LEFT JOIN products p ON p.store_id = s.id
             LEFT JOIN orders o ON o.store_id = s.id
             WHERE s.id = :store_id AND s.status = 'active'
             GROUP BY s.id, s.trade_name, s.city, s.state"
        );
        $storeStatement->execute(['store_id' => $storeId]);
        $store = $storeStatement->fetch();

        if (!$store) {
            throw new ApiException(404, 'STORE_NOT_FOUND', 'Loja nao encontrada.');
        }

        $productStatement = $this->pdo->prepare(
            "SELECT
                p.id,
                p.name,
                p.description,
                p.base_price,
                p.stock_quantity,
                c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.store_id = :store_id
               AND p.status = 'active'
               AND p.stock_quantity > 0
             ORDER BY c.name ASC, p.name ASC"
        );
        $productStatement->execute(['store_id' => $storeId]);

        return [
            'store' => [
                'id' => $store['id'],
                'trade_name' => $store['trade_name'],
            ],
            'products' => array_map(
                fn (array $row) => [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'] ?: '',
                    'price_value' => (float) ($row['base_price'] ?? 0),
                    'stock_quantity' => (int) ($row['stock_quantity'] ?? 0),
                    'category_name' => $row['category_name'] ?: 'Geral',
                ],
                $productStatement->fetchAll() ?: []
            ),
        ];
    }

    private function mapOrderStatusLabel(string $status): string
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

    private function resolveProgress(string $status): string
    {
        return match ($status) {
            'pending_acceptance' => 'A loja recebeu o pedido e está analisando o aceite.',
            'accepted' => 'Pedido aceito. A loja vai iniciar o preparo.',
            'preparing' => 'A loja está preparando o pedido.',
            'ready_for_pickup' => 'Pedido pronto para retirada pela operação.',
            'on_route' => 'Pedido saiu para entrega.',
            'completed' => 'Pedido concluído com sucesso.',
            'cancelled' => 'Pedido cancelado pela operação.',
            default => 'Pedido em processamento.',
        };
    }

    private function formatCurrency(float $amount): string
    {
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }
}
