<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Partner\PartnerOperationsRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoPartnerOperationsRepository implements PartnerOperationsRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function getDashboard(string $userId): array
    {
        $store = $this->resolveStore($userId);
        $todayMetrics = $this->loadTodayMetrics($store['id']);
        $catalogMetrics = $this->loadCatalogMetrics($store['id']);
        $latestOrders = $this->loadOrdersByStore($store['id'], 3);
        $topProducts = $this->loadTopProducts($store['id'], 3);

        return [
            'store' => [
                'id' => $store['id'],
                'trade_name' => $store['trade_name'],
                'status' => $store['status'],
                'city' => $store['city'],
                'state' => $store['state'],
            ],
            'heroTitle' => 'Acompanhe pedidos, catalogo e desempenho da sua loja em um unico painel.',
            'heroLead' => 'Tudo o que a operacao parceira precisa para vender, atualizar pedidos e manter o catalogo organizado em um painel unico.',
            'summary' => [
                ['label' => 'Pedidos do dia', 'value' => (string) $todayMetrics['orders_today']],
                ['label' => 'Faturamento bruto do dia', 'value' => $this->formatMoney($todayMetrics['gross_revenue_today'])],
                ['label' => 'Ticket medio do dia', 'value' => $this->formatMoney($todayMetrics['average_ticket_today'])],
            ],
            'metrics' => [
                ['label' => 'pedidos em andamento', 'value' => (string) $todayMetrics['in_progress_orders']],
                ['label' => 'pedidos concluidos', 'value' => (string) $todayMetrics['completed_orders']],
                ['label' => 'itens ativos no catalogo', 'value' => (string) $catalogMetrics['active_products']],
                ['label' => 'itens com estoque baixo', 'value' => (string) $catalogMetrics['attention_products']],
            ],
            'orders' => $latestOrders,
            'top_products' => $topProducts,
            'health' => [
                ['title' => 'Catalogo', 'text' => sprintf('%d itens ativos e %d itens exigindo reposicao.', $catalogMetrics['active_products'], $catalogMetrics['attention_products'])],
                ['title' => 'Horarios', 'text' => sprintf('Loja operando em %s/%s com status %s.', $store['city'], $store['state'], $this->normalizeStatusLabel($store['status']))],
                ['title' => 'Pedidos', 'text' => sprintf('%d pedidos em andamento e %d concluidos hoje.', $todayMetrics['in_progress_orders'], $todayMetrics['completed_orders'])],
                ['title' => 'Financeiro', 'text' => sprintf('Faturamento bruto do dia em %s.', $this->formatMoney($todayMetrics['gross_revenue_today']))],
            ],
        ];
    }

    public function getOrders(string $userId): array
    {
        $store = $this->resolveStore($userId);
        $rows = $this->loadOrdersByStore($store['id']);

        return [
            'store' => [
                'id' => $store['id'],
                'trade_name' => $store['trade_name'],
            ],
            'totals' => [
                'total' => count($rows),
                'pending' => count(array_filter($rows, static fn (array $row) => $row['status_key'] === 'pending_acceptance')),
                'critical' => count(array_filter($rows, static fn (array $row) => in_array($row['status_key'], ['pending_acceptance', 'cancelled'], true))),
            ],
            'orders' => $rows,
        ];
    }

    public function updateOrderStatus(string $userId, string $orderId, array $data): array
    {
        $store = $this->resolveStore($userId);
        $current = $this->findOrder($store['id'], $orderId);
        if (!$current) {
            throw new ApiException(404, 'ORDER_NOT_FOUND', 'Pedido nao encontrado para esta loja.');
        }

        $this->pdo->beginTransaction();

        try {
            $acceptedAt = $current['accepted_at'];
            $completedAt = $current['completed_at'];
            $cancelledAt = $current['cancelled_at'];

            if ($data['status'] === 'accepted' && !$acceptedAt) {
                $acceptedAt = date(DATE_ATOM);
            }

            if ($data['status'] === 'completed') {
                $completedAt = date(DATE_ATOM);
            }

            if ($data['status'] === 'cancelled') {
                $cancelledAt = date(DATE_ATOM);
            }

            $update = $this->pdo->prepare(
                "UPDATE orders
                 SET status = :status,
                     accepted_at = COALESCE(CAST(:accepted_at AS timestamptz), accepted_at),
                     completed_at = CASE WHEN :completed_at IS NULL THEN completed_at ELSE CAST(:completed_at AS timestamptz) END,
                     cancelled_at = CASE WHEN :cancelled_at IS NULL THEN cancelled_at ELSE CAST(:cancelled_at AS timestamptz) END,
                     updated_at = NOW()
                 WHERE id = :order_id AND store_id = :store_id"
            );
            $update->execute([
                'status' => $data['status'],
                'accepted_at' => $acceptedAt,
                'completed_at' => $completedAt,
                'cancelled_at' => $cancelledAt,
                'order_id' => $orderId,
                'store_id' => $store['id'],
            ]);

            $log = $this->pdo->prepare(
                "INSERT INTO order_status_logs (
                    id, order_id, previous_status, next_status, actor_user_id, note
                 ) VALUES (
                    gen_random_uuid(), :order_id, :previous_status, :next_status, :actor_user_id, :note
                 )"
            );
            $log->execute([
                'order_id' => $orderId,
                'previous_status' => $current['status'],
                'next_status' => $data['status'],
                'actor_user_id' => $userId,
                'note' => $data['note'] !== '' ? $data['note'] : 'Atualizacao manual via Partner Portal',
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getOrders($userId);
    }

    private function resolveStore(string $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT s.id, s.trade_name, s.status, s.city, s.state
             FROM partner_accounts ap
             INNER JOIN stores s ON s.partner_account_id = ap.id
             WHERE ap.owner_user_id = :user_id
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);
        $store = $statement->fetch();

        if (!$store) {
            throw new ApiException(404, 'PARTNER_STORE_NOT_FOUND', 'Nao foi possivel localizar a loja do parceiro.');
        }

        return $store;
    }

    private function loadTodayMetrics(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                COUNT(*) FILTER (WHERE DATE(placed_at) = CURRENT_DATE) AS orders_today,
                COUNT(*) FILTER (WHERE status IN ('accepted', 'preparing', 'ready_for_pickup', 'on_route')) AS in_progress_orders,
                COUNT(*) FILTER (WHERE status = 'completed' AND DATE(placed_at) = CURRENT_DATE) AS completed_orders,
                COALESCE(SUM(total) FILTER (WHERE DATE(placed_at) = CURRENT_DATE AND status <> 'cancelled'), 0) AS gross_revenue_today,
                COALESCE(AVG(total) FILTER (WHERE DATE(placed_at) = CURRENT_DATE AND status <> 'cancelled'), 0) AS average_ticket_today
             FROM orders
             WHERE store_id = :store_id"
        );
        $statement->execute(['store_id' => $storeId]);

        return $statement->fetch() ?: [];
    }

    private function loadCatalogMetrics(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                COUNT(*) FILTER (WHERE status = 'active') AS active_products,
                COUNT(*) FILTER (WHERE stock_quantity <= min_stock_quantity OR stock_quantity = 0 OR status = 'paused') AS attention_products
             FROM products
             WHERE store_id = :store_id"
        );
        $statement->execute(['store_id' => $storeId]);

        return $statement->fetch() ?: [];
    }

    private function loadTopProducts(string $storeId, int $limit): array
    {
        $statement = $this->pdo->prepare(
            "SELECT p.name, COALESCE(c.name, 'Sem categoria') AS category_name, p.sold_count, p.status, p.stock_quantity, p.min_stock_quantity
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.store_id = :store_id
             ORDER BY p.sold_count DESC, p.name ASC
             LIMIT :limit"
        );
        $statement->bindValue(':store_id', $storeId);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map(fn (array $row) => [
            'name' => $row['name'],
            'category' => $row['category_name'],
            'sold_count' => (int) $row['sold_count'],
            'status' => ((int) $row['stock_quantity'] <= (int) $row['min_stock_quantity']) ? 'estoque baixo' : $this->normalizeStatusLabel($row['status']),
            'status_type' => ((int) $row['stock_quantity'] <= (int) $row['min_stock_quantity']) ? 'warning' : ($row['status'] === 'paused' ? 'danger' : 'success'),
        ], $statement->fetchAll() ?: []);
    }

    private function loadOrdersByStore(string $storeId, ?int $limit = null): array
    {
        $sql = "SELECT o.id, o.order_number, o.customer_name, o.status, o.total, o.placed_at, o.completed_at,
                       COALESCE(du.full_name, 'sem atribuicao') AS driver_name
                FROM orders o
                LEFT JOIN driver_profiles dp ON dp.id = o.driver_profile_id
                LEFT JOIN users du ON du.id = dp.user_id
                WHERE o.store_id = :store_id
                ORDER BY o.placed_at DESC";

        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
        }

        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':store_id', $storeId);
        if ($limit !== null) {
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $statement->execute();

        return array_map(fn (array $row) => $this->formatOrderRow($row), $statement->fetchAll() ?: []);
    }

    private function findOrder(string $storeId, string $orderId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT id, status, accepted_at, completed_at, cancelled_at
             FROM orders
             WHERE id = :order_id AND store_id = :store_id
             LIMIT 1"
        );
        $statement->execute([
            'order_id' => $orderId,
            'store_id' => $storeId,
        ]);

        return $statement->fetch();
    }

    private function formatOrderRow(array $row): array
    {
        [$statusLabel, $statusType, $action] = match ($row['status']) {
            'pending_acceptance' => ['aguardando aceite', 'warning', 'Aceitar'],
            'accepted' => ['aceito', 'success', 'Iniciar preparo'],
            'preparing' => ['em preparo', 'success', 'Atualizar'],
            'ready_for_pickup' => ['pronto para retirada', 'warning', 'Sinalizar coleta'],
            'on_route' => ['em rota', 'success', 'Acompanhar entrega'],
            'completed' => ['concluido', 'success', 'Ver detalhes'],
            'cancelled' => ['cancelado', 'danger', 'Registrar motivo'],
            default => [$row['status'], 'warning', 'Atualizar'],
        };

        return [
            'id' => '#' . $row['order_number'],
            'order_id' => $row['id'],
            'customer' => $row['customer_name'],
            'status' => $statusLabel,
            'status_key' => $row['status'],
            'statusType' => $statusType,
            'sla' => $this->buildSlaLabel($row['placed_at'], $row['completed_at'], $row['status']),
            'value' => $this->formatMoney($row['total']),
            'action' => $action,
            'driver_name' => $row['driver_name'],
        ];
    }

    private function buildSlaLabel(?string $placedAt, ?string $completedAt, string $status): string
    {
        if ($status === 'completed') {
            return 'entregue';
        }

        if ($status === 'cancelled') {
            return 'fora da janela';
        }

        if (!$placedAt) {
            return '-';
        }

        return sprintf('%d min', max(1, (int) floor((time() - strtotime($placedAt)) / 60)));
    }

    private function normalizeStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'ativo',
            'paused' => 'pausado',
            'pending' => 'pendente',
            'suspended' => 'suspenso',
            default => $status,
        };
    }

    private function formatMoney(float|int|string|null $value): string
    {
        return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
