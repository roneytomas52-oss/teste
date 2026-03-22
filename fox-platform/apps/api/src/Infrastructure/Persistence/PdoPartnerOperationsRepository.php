<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Partner\PartnerOperationsRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoPartnerOperationsRepository implements PartnerOperationsRepository
{
    use SupportsSqlDialect;

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

    public function getOrderDetail(string $userId, string $orderId): array
    {
        $store = $this->resolveStore($userId);
        $order = $this->loadOrderDetailRow($store['id'], $orderId);

        if (!$order) {
            throw new ApiException(404, 'ORDER_NOT_FOUND', 'Pedido nao encontrado para esta loja.');
        }

        return [
            'order' => $this->formatOrderDetailRow($order),
            'items' => $this->loadOrderItems($orderId),
            'timeline' => $this->loadOrderTimeline($orderId),
        ];
    }

    public function getFinance(string $userId): array
    {
        $store = $this->resolveStore($userId);
        $snapshot = $this->loadFinanceSnapshot($store['id']);
        $payouts = $this->loadPayoutsByStore($store['id']);
        $bankAccount = $this->loadBankAccountByStore($store['id']);
        $transactions = $this->loadTransactionsByStore($store['id']);

        return [
            'store' => [
                'id' => $store['id'],
                'trade_name' => $store['trade_name'],
            ],
            'balance' => $this->formatMoney($snapshot['available_balance']),
            'balanceNote' => 'Saldo consolidado com base em receitas processadas, taxas da plataforma e repasses ja registrados para a loja.',
            'stats' => [
                ['label' => 'previsto para o proximo ciclo', 'value' => $this->formatMoney($payouts[0]['amount_raw'] ?? 0)],
                ['label' => 'taxas e comissoes do periodo', 'value' => $this->formatMoney($snapshot['fees_total'])],
                ['label' => 'ajustes pendentes', 'value' => (string) $snapshot['pending_adjustments']],
            ],
            'payouts' => array_map(fn (array $item) => [
                'date' => $this->formatDate($item['scheduled_for']),
                'title' => sprintf('Repasse referente ao periodo %s a %s.', $this->formatShortDate($item['period_start']), $this->formatShortDate($item['period_end'])),
                'text' => $item['note'] ?: 'Repasse consolidado a partir dos pedidos concluidos e taxas do ciclo.',
                'status' => $this->normalizePayoutStatusLabel($item['status']),
                'status_type' => $this->normalizePayoutStatusType($item['status']),
                'amount' => $this->formatMoney($item['amount_raw']),
            ], $payouts),
            'bank_account' => [
                'bank_name' => $bankAccount['bank_name'] ?? '-',
                'branch_number' => $bankAccount['branch_number'] ?? '-',
                'account_number' => $bankAccount['account_number'] ?? '-',
                'status' => $this->normalizeBankAccountStatusLabel($bankAccount['status'] ?? 'pending'),
                'status_type' => $this->normalizeBankAccountStatusType($bankAccount['status'] ?? 'pending'),
            ],
            'transactions' => $transactions,
        ];
    }

    public function getSupport(string $userId): array
    {
        $store = $this->resolveStore($userId);

        return [
            'tickets' => $this->loadSupportTicketsByStore($store['id']),
        ];
    }

    public function createSupportTicket(string $userId, array $data): array
    {
        $store = $this->resolveStore($userId);

        $this->pdo->beginTransaction();

        try {
            $ticketId = $this->newUuid();
            $ticketInsert = $this->pdo->prepare(
                sprintf(
                "INSERT INTO support_tickets (
                    id, scope, partner_account_id, store_id, created_by_user_id, channel, assigned_team, priority, status, subject, description, last_message_at
                 ) VALUES (
                    :id, 'partner', :partner_account_id, :store_id, :created_by_user_id, :channel, :assigned_team, :priority, 'open', :subject, :description, NOW()
                 )"
                )
            );
            $ticketInsert->execute([
                'id' => $ticketId,
                'partner_account_id' => $store['partner_account_id'],
                'store_id' => $store['id'],
                'created_by_user_id' => $userId,
                'channel' => strtolower($data['channel']),
                'assigned_team' => $this->resolveSupportTeam($data['channel']),
                'priority' => $data['priority'],
                'subject' => $data['subject'],
                'description' => $data['description'],
            ]);

            $messageInsert = $this->pdo->prepare(
                sprintf(
                "INSERT INTO support_messages (
                    id, ticket_id, sender_user_id, sender_role, body
                 ) VALUES (
                    %s, :ticket_id, :sender_user_id, 'partner_owner', :body
                 )",
                 $this->uuidExpression()
                )
            );
            $messageInsert->execute([
                'ticket_id' => $ticketId,
                'sender_user_id' => $userId,
                'body' => $data['description'],
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getSupport($userId);
    }

    public function getSupportThread(string $userId, string $ticketId): array
    {
        $store = $this->resolveStore($userId);
        $ticket = $this->findSupportTicketByStore($store['id'], $ticketId);

        if (!$ticket) {
            throw new ApiException(404, 'SUPPORT_TICKET_NOT_FOUND', 'Chamado nao encontrado para esta loja.');
        }

        return [
            'ticket' => $this->formatSupportTicket($ticket),
            'messages' => $this->loadSupportThreadMessages((string) $ticket['id'], 'partner_owner'),
        ];
    }

    public function replySupportThread(string $userId, string $ticketId, array $data): array
    {
        $store = $this->resolveStore($userId);
        $ticket = $this->findSupportTicketByStore($store['id'], $ticketId);

        if (!$ticket) {
            throw new ApiException(404, 'SUPPORT_TICKET_NOT_FOUND', 'Chamado nao encontrado para esta loja.');
        }

        $this->pdo->beginTransaction();

        try {
            $messageInsert = $this->pdo->prepare(
                sprintf(
                "INSERT INTO support_messages (
                    id, ticket_id, sender_user_id, sender_role, body
                 ) VALUES (
                    %s, :ticket_id, :sender_user_id, 'partner_owner', :body
                 )",
                 $this->uuidExpression()
                )
            );
            $messageInsert->execute([
                'ticket_id' => $ticket['id'],
                'sender_user_id' => $userId,
                'body' => $data['body'],
            ]);

            $ticketUpdate = $this->pdo->prepare(
                "UPDATE support_tickets
                 SET status = CASE WHEN status = 'resolved' THEN 'in_progress' ELSE status END,
                     last_message_at = NOW(),
                     updated_at = NOW()
                 WHERE id = :ticket_id"
            );
            $ticketUpdate->execute([
                'ticket_id' => $ticket['id'],
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getSupportThread($userId, $ticketId);
    }

    public function getNotifications(string $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, level, context, title, body, action_label, action_url, is_read, created_at
             FROM notifications
             WHERE scope = 'partner' AND user_id = :user_id
             ORDER BY is_read ASC, created_at DESC
             LIMIT 30"
        );
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll() ?: [];

        return [
            'summary' => [
                [
                    'label' => 'nao lidas',
                    'value' => (string) count(array_filter($rows, static fn (array $row) => !(bool) $row['is_read'])),
                ],
                [
                    'label' => 'exigem atencao',
                    'value' => (string) count(array_filter($rows, static fn (array $row) => in_array($row['level'], ['warning', 'danger'], true))),
                ],
                [
                    'label' => 'total recente',
                    'value' => (string) count($rows),
                ],
            ],
            'items' => array_map(fn (array $row) => $this->formatNotificationRow($row), $rows),
        ];
    }

    public function markNotificationRead(string $userId, string $notificationId): array
    {
        $statement = $this->pdo->prepare(
            "UPDATE notifications
             SET is_read = TRUE,
                 read_at = NOW()
             WHERE id = :notification_id
               AND scope = 'partner'
               AND user_id = :user_id"
        );
        $statement->execute([
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);

        if ($statement->rowCount() === 0) {
            throw new ApiException(404, 'NOTIFICATION_NOT_FOUND', 'Notificacao nao encontrada para esta conta.');
        }

        return $this->getNotifications($userId);
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
                     accepted_at = COALESCE(:accepted_at, accepted_at),
                     completed_at = CASE WHEN :completed_at IS NULL THEN completed_at ELSE :completed_at END,
                     cancelled_at = CASE WHEN :cancelled_at IS NULL THEN cancelled_at ELSE :cancelled_at END,
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
                sprintf(
                "INSERT INTO order_status_logs (
                    id, order_id, previous_status, next_status, actor_user_id, note
                 ) VALUES (
                    %s, :order_id, :previous_status, :next_status, :actor_user_id, :note
                 )",
                 $this->uuidExpression()
                )
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
            "SELECT id, trade_name, status, city, state, partner_account_id
             FROM (
                SELECT s.id, s.trade_name, s.status, s.city, s.state, s.partner_account_id
                FROM partner_accounts ap
                INNER JOIN stores s ON s.partner_account_id = ap.id
                WHERE ap.owner_user_id = :owner_user_id

                UNION ALL

                SELECT s.id, s.trade_name, s.status, s.city, s.state, s.partner_account_id
                FROM store_team_members stm
                INNER JOIN stores s ON s.id = stm.store_id
                WHERE stm.user_id = :team_user_id
                  AND stm.status = 'active'
             ) partner_store
             LIMIT 1"
        );
        $statement->execute([
            'owner_user_id' => $userId,
            'team_user_id' => $userId,
        ]);
        $store = $statement->fetch();

        if (!$store) {
            throw new ApiException(404, 'PARTNER_STORE_NOT_FOUND', 'Nao foi possivel localizar a loja do parceiro.');
        }

        return $store;
    }

    private function loadSupportTicketsByStore(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, channel, priority, status, subject, assigned_team, created_at, last_message_at
             FROM support_tickets
             WHERE store_id = :store_id
             ORDER BY last_message_at DESC, created_at DESC
             LIMIT 20"
        );
        $statement->execute(['store_id' => $storeId]);

        return array_map(fn (array $row) => $this->formatSupportTicket($row), $statement->fetchAll() ?: []);
    }

    private function loadTodayMetrics(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                SUM(CASE WHEN DATE(placed_at) = CURRENT_DATE THEN 1 ELSE 0 END) AS orders_today,
                SUM(CASE WHEN status IN ('accepted', 'preparing', 'ready_for_pickup', 'on_route') THEN 1 ELSE 0 END) AS in_progress_orders,
                SUM(CASE WHEN status = 'completed' AND DATE(placed_at) = CURRENT_DATE THEN 1 ELSE 0 END) AS completed_orders,
                COALESCE(SUM(CASE WHEN DATE(placed_at) = CURRENT_DATE AND status <> 'cancelled' THEN total ELSE 0 END), 0) AS gross_revenue_today,
                COALESCE(AVG(CASE WHEN DATE(placed_at) = CURRENT_DATE AND status <> 'cancelled' THEN total END), 0) AS average_ticket_today
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
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_products,
                SUM(CASE WHEN stock_quantity <= min_stock_quantity OR stock_quantity = 0 OR status = 'paused' THEN 1 ELSE 0 END) AS attention_products
             FROM products
             WHERE store_id = :store_id"
        );
        $statement->execute(['store_id' => $storeId]);

        return $statement->fetch() ?: [];
    }

    private function loadFinanceSnapshot(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END), 0) AS available_balance,
                COALESCE(SUM(CASE WHEN transaction_type = 'platform_fee' THEN amount ELSE 0 END), 0) AS fees_total,
                SUM(CASE WHEN transaction_type IN ('adjustment', 'refund') AND status = 'under_review' THEN 1 ELSE 0 END) AS pending_adjustments
             FROM wallet_transactions
             WHERE store_id = :store_id"
        );
        $statement->execute(['store_id' => $storeId]);

        return $statement->fetch() ?: [];
    }

    private function loadPayoutsByStore(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT period_start, period_end, scheduled_for, amount AS amount_raw, status, note
             FROM payout_requests
             WHERE store_id = :store_id
             ORDER BY scheduled_for ASC
             LIMIT 4"
        );
        $statement->execute(['store_id' => $storeId]);

        return $statement->fetchAll() ?: [];
    }

    private function loadBankAccountByStore(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT bank_name, branch_number, account_number, status
             FROM store_bank_accounts
             WHERE store_id = :store_id
             ORDER BY updated_at DESC
             LIMIT 1"
        );
        $statement->execute(['store_id' => $storeId]);

        return $statement->fetch() ?: [];
    }

    private function loadTransactionsByStore(string $storeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT occurred_at, description, transaction_type, status, amount, direction
             FROM wallet_transactions
             WHERE store_id = :store_id
             ORDER BY occurred_at DESC
             LIMIT 12"
        );
        $statement->execute(['store_id' => $storeId]);

        return array_map(fn (array $row) => [
            'date' => $this->formatDate($row['occurred_at']),
            'description' => $row['description'],
            'type' => $this->normalizeTransactionTypeLabel($row['transaction_type']),
            'status' => $this->normalizeTransactionStatusLabel($row['status']),
            'status_type' => $this->normalizeTransactionStatusType($row['status']),
            'value' => sprintf(
                '%s %s',
                $row['direction'] === 'credit' ? '+' : '-',
                $this->formatMoney($row['amount'])
            ),
        ], $statement->fetchAll() ?: []);
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

    private function loadOrderDetailRow(string $storeId, string $orderId): array|false
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
                s.trade_name AS store_name,
                COALESCE(du.full_name, 'sem atribuicao') AS driver_name
             FROM orders o
             INNER JOIN stores s ON s.id = o.store_id
             LEFT JOIN driver_profiles dp ON dp.id = o.driver_profile_id
             LEFT JOIN users du ON du.id = dp.user_id
             WHERE o.id = :order_id
               AND o.store_id = :store_id
             LIMIT 1"
        );
        $statement->execute([
            'order_id' => $orderId,
            'store_id' => $storeId,
        ]);

        return $statement->fetch();
    }

    private function loadOrderItems(string $orderId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT product_name, quantity, unit_price, total_price, notes
             FROM order_items
             WHERE order_id = :order_id
             ORDER BY created_at ASC"
        );
        $statement->execute(['order_id' => $orderId]);

        return array_map(fn (array $row) => [
            'name' => $row['product_name'],
            'quantity' => (int) $row['quantity'],
            'unit_price' => $this->formatMoney($row['unit_price']),
            'total_price' => $this->formatMoney($row['total_price']),
            'notes' => $row['notes'] ?: '-',
        ], $statement->fetchAll() ?: []);
    }

    private function loadOrderTimeline(string $orderId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT osl.previous_status, osl.next_status, osl.note, osl.created_at, u.full_name AS actor_name
             FROM order_status_logs osl
             LEFT JOIN users u ON u.id = osl.actor_user_id
             WHERE osl.order_id = :order_id
             ORDER BY osl.created_at ASC"
        );
        $statement->execute(['order_id' => $orderId]);

        return array_map(fn (array $row) => [
            'title' => sprintf('Status atualizado para %s', $this->normalizeOrderStatusLabel((string) $row['next_status'])),
            'description' => $row['note'] ?: 'Atualizacao operacional registrada no portal.',
            'actor' => $row['actor_name'] ?: 'Fox Platform',
            'created_at' => $this->formatDateTime((string) $row['created_at']),
        ], $statement->fetchAll() ?: []);
    }

    private function findSupportTicketByStore(string $storeId, string $ticketId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT id, channel, priority, status, subject, assigned_team, created_at, last_message_at
             FROM support_tickets
             WHERE store_id = :store_id
               AND id = :ticket_id
             LIMIT 1"
        );
        $statement->execute([
            'store_id' => $storeId,
            'ticket_id' => $ticketId,
        ]);

        return $statement->fetch();
    }

    private function loadSupportThreadMessages(string $ticketId, string $outgoingRole): array
    {
        $statement = $this->pdo->prepare(
            "SELECT sm.id, sm.sender_role, sm.body, sm.created_at, u.full_name
             FROM support_messages sm
             LEFT JOIN users u ON u.id = sm.sender_user_id
             WHERE sm.ticket_id = :ticket_id
             ORDER BY sm.created_at ASC"
        );
        $statement->execute(['ticket_id' => $ticketId]);

        return array_map(function (array $row) use ($outgoingRole): array {
            $direction = $row['sender_role'] === $outgoingRole ? 'outgoing' : 'incoming';

            return [
                'id' => $row['id'],
                'direction' => $direction,
                'author' => $direction === 'outgoing'
                    ? 'Loja parceira'
                    : ($row['full_name'] ?: 'Fox Delivery'),
                'body' => $row['body'],
                'time' => $this->formatDateTime((string) $row['created_at']),
                'role' => $row['sender_role'],
            ];
        }, $statement->fetchAll() ?: []);
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

    private function formatOrderDetailRow(array $row): array
    {
        [$statusLabel, $statusType] = $this->normalizeOrderStatusMeta((string) $row['status']);

        return [
            'id' => '#' . $row['order_number'],
            'order_id' => $row['id'],
            'store_name' => $row['store_name'],
            'customer' => $row['customer_name'],
            'customer_phone' => $row['customer_phone'] ?: '-',
            'customer_address' => $row['customer_address'] ?: '-',
            'driver_name' => $row['driver_name'],
            'status' => $statusLabel,
            'status_key' => $row['status'],
            'status_type' => $statusType,
            'payment_method' => $this->normalizePaymentMethodLabel((string) $row['payment_method']),
            'payment_status' => $this->normalizePaymentStatusLabel((string) $row['payment_status']),
            'subtotal' => $this->formatMoney($row['subtotal']),
            'delivery_fee' => $this->formatMoney($row['delivery_fee']),
            'total' => $this->formatMoney($row['total']),
            'placed_at' => $this->formatDateTime((string) $row['placed_at']),
            'accepted_at' => $row['accepted_at'] ? $this->formatDateTime((string) $row['accepted_at']) : '-',
            'completed_at' => $row['completed_at'] ? $this->formatDateTime((string) $row['completed_at']) : '-',
            'cancelled_at' => $row['cancelled_at'] ? $this->formatDateTime((string) $row['cancelled_at']) : '-',
            'sla' => $this->buildSlaLabel($row['placed_at'], $row['completed_at'], $row['status']),
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

    private function normalizeOrderStatusMeta(string $status): array
    {
        return match ($status) {
            'pending_acceptance' => ['aguardando aceite', 'warning'],
            'accepted' => ['aceito', 'success'],
            'preparing' => ['em preparo', 'success'],
            'ready_for_pickup' => ['pronto para retirada', 'warning'],
            'on_route' => ['em rota', 'success'],
            'completed' => ['concluido', 'success'],
            'cancelled' => ['cancelado', 'danger'],
            default => [$status, 'warning'],
        };
    }

    private function normalizeOrderStatusLabel(string $status): string
    {
        return $this->normalizeOrderStatusMeta($status)[0];
    }

    private function normalizePaymentMethodLabel(string $method): string
    {
        return match ($method) {
            'online_card' => 'Cartao online',
            'cash' => 'Dinheiro',
            'pix' => 'Pix',
            default => ucfirst(str_replace('_', ' ', $method)),
        };
    }

    private function normalizePaymentStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'refunded' => 'Reembolsado',
            default => ucfirst($status),
        };
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

    private function normalizePayoutStatusLabel(string $status): string
    {
        return match ($status) {
            'scheduled' => 'previsto',
            'processing' => 'em processamento',
            'completed' => 'concluido',
            'blocked' => 'bloqueado',
            default => 'em analise',
        };
    }

    private function normalizeSupportChannelLabel(string $channel): string
    {
        return match ($channel) {
            'operations' => 'Operacao',
            'finance' => 'Financeiro',
            'catalog' => 'Catalogo',
            'commercial' => 'Comercial',
            default => ucfirst($channel),
        };
    }

    private function normalizeSupportPriorityLabel(string $priority): string
    {
        return match ($priority) {
            'critical' => 'critica',
            'high' => 'alta',
            default => 'normal',
        };
    }

    private function normalizeSupportStatusLabel(string $status): string
    {
        return match ($status) {
            'open' => 'aberto',
            'in_progress' => 'em andamento',
            'answered' => 'respondido',
            'resolved' => 'concluido',
            default => 'em analise',
        };
    }

    private function normalizeSupportStatusType(string $status): string
    {
        return match ($status) {
            'resolved' => 'success',
            'in_progress' => 'warning',
            'answered' => 'success',
            default => 'warning',
        };
    }

    private function formatSupportTicket(array $row): array
    {
        return [
            'id' => '#SUP-' . strtoupper(substr(str_replace('-', '', (string) $row['id']), 0, 6)),
            'ticket_id' => $row['id'],
            'channel' => $this->normalizeSupportChannelLabel((string) $row['channel']),
            'status' => $this->normalizeSupportStatusLabel((string) $row['status']),
            'statusType' => $this->normalizeSupportStatusType((string) $row['status']),
            'summary' => $row['subject'],
            'priority' => $this->normalizeSupportPriorityLabel((string) $row['priority']),
            'assigned_team' => $row['assigned_team'] ?: 'operacao',
            'meta' => [
                'Prioridade ' . $this->normalizeSupportPriorityLabel((string) $row['priority']),
                'Atualizado em ' . $this->formatDate((string) $row['last_message_at']),
            ],
            'created_at' => $row['created_at'] ?? null,
            'last_message_at' => $row['last_message_at'] ?? null,
        ];
    }

    private function formatNotificationRow(array $row): array
    {
        return [
            'id' => $row['id'],
            'title' => $row['title'],
            'body' => $row['body'],
            'level' => $this->normalizeNotificationLevelLabel((string) $row['level']),
            'level_type' => $this->normalizeNotificationLevelType((string) $row['level']),
            'context' => $this->normalizeNotificationContextLabel((string) $row['context']),
            'action_label' => $row['action_label'],
            'action_url' => $row['action_url'],
            'is_read' => filter_var($row['is_read'], FILTER_VALIDATE_BOOL),
            'created_at' => $this->formatDateTime((string) $row['created_at']),
        ];
    }

    private function normalizeNotificationLevelLabel(string $level): string
    {
        return match ($level) {
            'success' => 'informacao confirmada',
            'warning' => 'requer atencao',
            'danger' => 'acao imediata',
            default => 'informativo',
        };
    }

    private function normalizeNotificationLevelType(string $level): string
    {
        return match ($level) {
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            default => 'info',
        };
    }

    private function normalizeNotificationContextLabel(string $context): string
    {
        return match ($context) {
            'orders' => 'Pedidos',
            'catalog' => 'Catalogo',
            'finance' => 'Financeiro',
            'team' => 'Equipe',
            default => 'Operacao',
        };
    }

    private function resolveSupportTeam(string $channel): string
    {
        return match (strtolower($channel)) {
            'finance' => 'financeiro',
            'catalog' => 'catalogo',
            'commercial' => 'comercial',
            default => 'operacao',
        };
    }

    private function normalizePayoutStatusType(string $status): string
    {
        return match ($status) {
            'completed' => 'success',
            'blocked' => 'danger',
            default => 'warning',
        };
    }

    private function normalizeBankAccountStatusLabel(string $status): string
    {
        return match ($status) {
            'validated' => 'validada',
            'rejected' => 'rejeitada',
            default => 'em analise',
        };
    }

    private function normalizeBankAccountStatusType(string $status): string
    {
        return match ($status) {
            'validated' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    private function normalizeTransactionTypeLabel(string $type): string
    {
        return match ($type) {
            'order_revenue' => 'credito operacional',
            'platform_fee' => 'taxa da plataforma',
            'payout' => 'repasse',
            'adjustment' => 'ajuste',
            'refund' => 'estorno',
            default => $type,
        };
    }

    private function normalizeTransactionStatusLabel(string $status): string
    {
        return match ($status) {
            'processed' => 'processado',
            'scheduled' => 'agendado',
            'sent' => 'enviado',
            'under_review' => 'revisar',
            default => $status,
        };
    }

    private function normalizeTransactionStatusType(string $status): string
    {
        return match ($status) {
            'processed', 'sent' => 'success',
            'under_review' => 'danger',
            default => 'warning',
        };
    }

    private function formatDate(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        return date('d/m/Y', strtotime($value));
    }

    private function formatDateTime(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        return date('d/m/Y H:i', strtotime($value));
    }

    private function formatShortDate(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        return date('d/m', strtotime($value));
    }

    private function formatMoney(float|int|string|null $value): string
    {
        return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
