<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoAdminOperationsRepository implements AdminOperationsRepository
{
    use SupportsSqlDialect;

    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function getDashboard(): array
    {
        $metrics = $this->loadPlatformMetrics();

        return [
            'heroTitle' => 'Controle parceiros, pedidos e saude operacional da Fox Delivery em um unico lugar.',
            'heroLead' => 'Aprovacoes, indicadores criticos, suporte e financeiro organizados por prioridade operacional.',
            'summary' => [
                ['label' => 'pedidos em andamento', 'value' => (string) $metrics['in_progress_orders']],
                ['label' => 'cadastros aguardando aprovacao', 'value' => (string) ($metrics['partner_pending'] + $metrics['driver_pending'])],
                ['label' => 'volume bruto processado hoje', 'value' => $this->formatMoney($metrics['gross_today'])],
            ],
            'metrics' => [
                ['label' => 'parceiros ativos na operacao', 'value' => (string) $metrics['active_partners']],
                ['label' => 'entregadores habilitados', 'value' => (string) $metrics['active_drivers']],
                ['label' => 'pedidos concluidos hoje', 'value' => (string) $metrics['completed_today']],
                ['label' => 'alertas criticos abertos', 'value' => (string) ($metrics['cancelled_today'] + $metrics['partner_pending'])],
            ],
            'approvals' => $this->getPartnerApprovals()['items'],
            'alerts' => [
                sprintf('%d lojas aguardando aprovacao comercial.', $metrics['partner_pending']),
                sprintf('%d entregadores aguardando validacao operacional.', $metrics['driver_pending']),
                sprintf('%d pedidos em andamento no ecossistema.', $metrics['in_progress_orders']),
            ],
        ];
    }

    public function getOrders(): array
    {
        $statement = $this->pdo->query(
            "SELECT o.id, o.order_number, o.customer_name, o.status, o.total, o.placed_at,
                    s.trade_name AS store_name, COALESCE(du.full_name, 'sem atribuicao') AS driver_name
             FROM orders o
             INNER JOIN stores s ON s.id = o.store_id
             LEFT JOIN driver_profiles dp ON dp.id = o.driver_profile_id
             LEFT JOIN users du ON du.id = dp.user_id
             ORDER BY o.placed_at DESC"
        );

        $items = array_map(fn (array $row) => $this->formatOrderRow($row), $statement->fetchAll() ?: []);

        return [
            'totals' => [
                'total' => count($items),
                'critical' => count(array_filter($items, static fn (array $item) => in_array($item['status_key'], ['pending_acceptance', 'cancelled'], true))),
            ],
            'items' => $items,
        ];
    }

    public function getOrderDetail(string $orderId): array
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
             LIMIT 1"
        );
        $statement->execute(['order_id' => $orderId]);
        $order = $statement->fetch();

        if (!$order) {
            throw new ApiException(404, 'ORDER_NOT_FOUND', 'Pedido nao encontrado para a operacao.');
        }

        return [
            'order' => $this->formatOrderDetailRow($order),
            'items' => $this->loadOrderItems($orderId),
            'timeline' => $this->loadOrderTimeline($orderId),
        ];
    }

    public function getFinance(): array
    {
        $metrics = $this->loadFinanceMetrics();
        $highlights = $this->loadFinanceHighlights();
        $payouts = $this->loadPayoutRows();

        return [
            'balance' => $this->formatMoney($metrics['gross_today']),
            'balanceNote' => 'Volume bruto do dia consolidado com taxas, repasses previstos e ajustes operacionais em apuracao na plataforma.',
            'stats' => [
                ['label' => 'comissoes apuradas no dia', 'value' => $this->formatMoney($metrics['fees_today'])],
                ['label' => 'repasses previstos nesta semana', 'value' => $this->formatMoney($metrics['payouts_week'])],
                ['label' => 'ajustes financeiros pendentes', 'value' => (string) $metrics['pending_adjustments']],
            ],
            'highlights' => $highlights,
            'payouts' => $payouts,
        ];
    }

    public function getPartnerApprovals(): array
    {
        $statement = $this->pdo->query(
            "SELECT s.id, s.trade_name, s.city, s.state, s.status
             FROM stores s
             WHERE s.status IN ('draft', 'pending', 'rejected')
             ORDER BY s.updated_at DESC"
        );

        $items = array_map(
            fn (array $row) => [
                'id' => $row['id'],
                'name' => $row['trade_name'],
                'summary' => 'Cadastro aguardando revisao documental, enquadramento comercial e conferencia operacional.',
                'meta' => array_values(array_filter([$row['city'], $row['state']])),
                'status' => $row['status'] === 'rejected' ? 'revisao manual' : 'documentacao pendente',
                'statusType' => $row['status'] === 'rejected' ? 'danger' : 'warning',
                'action' => $row['status'] === 'rejected' ? 'Solicitar correcoes' : 'Abrir cadastro',
            ],
            $statement->fetchAll() ?: []
        );

        return ['items' => $items];
    }

    public function reviewPartnerApproval(string $partnerId, string $decision): array
    {
        $nextStatus = $decision === 'approve' ? 'active' : 'rejected';

        $statement = $this->pdo->prepare(
            "UPDATE stores
             SET status = :status,
                 updated_at = NOW()
             WHERE id = :partner_id"
        );
        $statement->execute([
            'status' => $nextStatus,
            'partner_id' => $partnerId,
        ]);

        return $this->getPartnerApprovals();
    }

    public function getSupport(): array
    {
        return [
            'priorityQueue' => $this->loadSupportQueue(),
            'distribution' => $this->loadSupportDistribution(),
            'sla' => $this->loadSupportSla(),
        ];
    }

    public function getSupportThread(string $ticketId): array
    {
        $ticket = $this->findSupportTicket($ticketId);

        if (!$ticket) {
            throw new ApiException(404, 'SUPPORT_TICKET_NOT_FOUND', 'Chamado nao encontrado para a operacao.');
        }

        return [
            'ticket' => $this->formatSupportTicketDetail($ticket),
            'messages' => $this->loadSupportThreadMessages($ticketId),
        ];
    }

    public function replySupportThread(string $userId, string $ticketId, array $data): array
    {
        $ticket = $this->findSupportTicket($ticketId);

        if (!$ticket) {
            throw new ApiException(404, 'SUPPORT_TICKET_NOT_FOUND', 'Chamado nao encontrado para a operacao.');
        }

        $this->pdo->beginTransaction();

        try {
            $messageInsert = $this->pdo->prepare(
                sprintf(
                    "INSERT INTO support_messages (
                        id, ticket_id, sender_user_id, sender_role, body
                     ) VALUES (
                        %s, :ticket_id, :sender_user_id, 'admin', :body
                     )",
                    $this->uuidExpression()
                )
            );
            $messageInsert->execute([
                'ticket_id' => $ticketId,
                'sender_user_id' => $userId,
                'body' => $data['body'],
            ]);

            $ticketUpdate = $this->pdo->prepare(
                "UPDATE support_tickets
                 SET status = 'answered',
                     last_message_at = NOW(),
                     updated_at = NOW()
                 WHERE id = :ticket_id"
            );
            $ticketUpdate->execute([
                'ticket_id' => $ticketId,
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getSupportThread($ticketId);
    }

    public function updateSupportTicketStatus(string $userId, string $ticketId, array $data): array
    {
        $ticket = $this->findSupportTicket($ticketId);

        if (!$ticket) {
            throw new ApiException(404, 'SUPPORT_TICKET_NOT_FOUND', 'Chamado nao encontrado para a operacao.');
        }

        $this->pdo->beginTransaction();

        try {
            $ticketUpdate = $this->pdo->prepare(
                "UPDATE support_tickets
                 SET status = :status,
                     last_message_at = NOW(),
                     updated_at = NOW()
                 WHERE id = :ticket_id"
            );
            $ticketUpdate->execute([
                'status' => $data['status'],
                'ticket_id' => $ticketId,
            ]);

            if (($data['note'] ?? '') !== '') {
                $messageInsert = $this->pdo->prepare(
                    sprintf(
                        "INSERT INTO support_messages (
                            id, ticket_id, sender_user_id, sender_role, body
                         ) VALUES (
                            %s, :ticket_id, :sender_user_id, 'admin', :body
                         )",
                        $this->uuidExpression()
                    )
                );
                $messageInsert->execute([
                    'ticket_id' => $ticketId,
                    'sender_user_id' => $userId,
                    'body' => $data['note'],
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getSupportThread($ticketId);
    }

    public function getSettings(): array
    {
        $statement = $this->pdo->query(
            "SELECT group_slug, setting_key, value_json
             FROM platform_settings
             ORDER BY group_slug, setting_key"
        );

        $records = $statement->fetchAll() ?: [];
        $mapped = [];

        foreach ($records as $record) {
            $group = (string) $record['group_slug'];
            $mapped[$group] = array_merge(
                $mapped[$group] ?? [],
                json_decode((string) $record['value_json'], true) ?: []
            );
        }

        $branding = $mapped['branding'] ?? [];
        $operations = $mapped['operations'] ?? [];
        $notifications = $mapped['notifications'] ?? [];
        $security = $mapped['security'] ?? [];

        return [
            'branding' => [
                'platform_name' => $branding['platform_name'] ?? 'Fox Delivery',
                'support_email' => $branding['support_email'] ?? 'suporte@foxdelivery.com.br',
                'partner_login_url' => $branding['partner_login_url'] ?? 'https://foxgodelivery.com.br/login/parceiro',
                'support_phone' => $branding['support_phone'] ?? '+55 11 4000-1122',
            ],
            'operations' => [
                'default_order_sla_minutes' => (int) ($operations['default_order_sla_minutes'] ?? 45),
                'partner_review_window_hours' => (int) ($operations['partner_review_window_hours'] ?? 24),
                'driver_review_window_hours' => (int) ($operations['driver_review_window_hours'] ?? 24),
                'partner_auto_approval' => (bool) ($operations['partner_auto_approval'] ?? false),
                'driver_auto_approval' => (bool) ($operations['driver_auto_approval'] ?? false),
            ],
            'notifications' => [
                'partner_polling_seconds' => (int) ($notifications['partner_polling_seconds'] ?? $notifications['refresh_interval_seconds'] ?? 30),
                'driver_polling_seconds' => (int) ($notifications['driver_polling_seconds'] ?? $notifications['refresh_interval_seconds'] ?? 30),
                'admin_digest_enabled' => (bool) ($notifications['admin_digest_enabled'] ?? false),
                'partner_digest_enabled' => (bool) ($notifications['partner_digest_enabled'] ?? true),
                'driver_digest_enabled' => (bool) ($notifications['driver_digest_enabled'] ?? true),
            ],
            'security' => [
                'access_token_ttl_minutes' => (int) ($security['access_token_ttl_minutes'] ?? (($security['access_token_ttl_seconds'] ?? 900) / 60)),
                'refresh_token_ttl_days' => (int) ($security['refresh_token_ttl_days'] ?? (($security['refresh_token_ttl_seconds'] ?? 2592000) / 86400)),
                'password_reset_token_ttl_minutes' => (int) ($security['password_reset_token_ttl_minutes'] ?? (($security['password_reset_ttl_seconds'] ?? 3600) / 60)),
            ],
        ];
    }

    public function updateSettings(string $userId, array $settings): array
    {
        $mapping = [
            'branding' => 'platform_identity',
            'operations' => 'service_rules',
            'notifications' => 'delivery_rules',
            'security' => 'session_rules',
        ];

        $statement = $this->pdo->prepare(
            $this->isMySql()
                ? "INSERT INTO platform_settings (
                    id, group_slug, setting_key, value_json, is_public, updated_by_user_id
                 ) VALUES (
                    :id, :group_slug, :setting_key, :value_json, :is_public, :updated_by_user_id
                 )
                 ON DUPLICATE KEY UPDATE
                    value_json = VALUES(value_json),
                    is_public = VALUES(is_public),
                    updated_by_user_id = VALUES(updated_by_user_id),
                    updated_at = NOW()"
                : "INSERT INTO platform_settings (
                    id, group_slug, setting_key, value_json, is_public, updated_by_user_id
                 ) VALUES (
                    gen_random_uuid(), :group_slug, :setting_key, :value_json::jsonb, :is_public, :updated_by_user_id
                 )
                 ON CONFLICT (group_slug, setting_key) DO UPDATE
                 SET value_json = EXCLUDED.value_json,
                     is_public = EXCLUDED.is_public,
                     updated_by_user_id = EXCLUDED.updated_by_user_id,
                     updated_at = NOW()"
        );

        $this->pdo->beginTransaction();

        try {
            foreach ($settings as $group => $value) {
                $statement->execute([
                    'id' => $this->newUuid(),
                    'group_slug' => $group,
                    'setting_key' => $mapping[$group] ?? 'default',
                    'value_json' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'is_public' => $group === 'branding' ? 1 : 0,
                    'updated_by_user_id' => $userId,
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getSettings();
    }

    public function getDriverApprovals(): array
    {
        $statement = $this->pdo->query(
            "SELECT dp.id, u.full_name, dp.modal, dp.city, dp.state, dp.status
             FROM driver_profiles dp
             INNER JOIN users u ON u.id = dp.user_id
             WHERE dp.status IN ('pending', 'rejected')
             ORDER BY dp.updated_at DESC"
        );

        $items = array_map(
            fn (array $row) => [
                'id' => $row['id'],
                'name' => $row['full_name'],
                'summary' => 'Validacao de documentos, modalidade e readiness operacional antes da ativacao.',
                'meta' => array_values(array_filter([ucfirst((string) $row['modal']), $row['city'], $row['state']])),
                'status' => $row['status'] === 'rejected' ? 'revisao manual' : 'documentacao pendente',
                'statusType' => $row['status'] === 'rejected' ? 'danger' : 'warning',
                'action' => $row['status'] === 'rejected' ? 'Abrir documentos' : 'Solicitar ajuste',
            ],
            $statement->fetchAll() ?: []
        );

        return ['items' => $items];
    }

    public function reviewDriverApproval(string $driverId, string $decision): array
    {
        $nextStatus = $decision === 'approve' ? 'active' : 'rejected';

        $statement = $this->pdo->prepare(
            "UPDATE driver_profiles
             SET status = :status,
                 updated_at = NOW()
             WHERE id = :driver_id"
        );
        $statement->execute([
            'status' => $nextStatus,
            'driver_id' => $driverId,
        ]);

        return $this->getDriverApprovals();
    }

    private function loadPlatformMetrics(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                (SELECT COUNT(*) FROM stores WHERE status = 'active') AS active_partners,
                (SELECT COUNT(*) FROM stores WHERE status IN ('draft', 'pending', 'rejected')) AS partner_pending,
                (SELECT COUNT(*) FROM driver_profiles WHERE status = 'active') AS active_drivers,
                (SELECT COUNT(*) FROM driver_profiles WHERE status IN ('pending', 'rejected')) AS driver_pending,
                (SELECT COUNT(*) FROM orders WHERE status IN ('accepted', 'preparing', 'ready_for_pickup', 'on_route')) AS in_progress_orders,
                (SELECT COUNT(*) FROM orders WHERE status = 'completed' AND DATE(placed_at) = CURRENT_DATE) AS completed_today,
                (SELECT COUNT(*) FROM orders WHERE status = 'cancelled' AND DATE(placed_at) = CURRENT_DATE) AS cancelled_today,
                (SELECT COALESCE(SUM(total), 0) FROM orders WHERE DATE(placed_at) = CURRENT_DATE AND status <> 'cancelled') AS gross_today"
        );

        return $statement->fetch() ?: [];
    }

    private function loadFinanceMetrics(): array
    {
        $statement = $this->pdo->query(
            sprintf(
                "SELECT
                    COALESCE((SELECT SUM(total) FROM orders WHERE DATE(placed_at) = CURRENT_DATE AND status <> 'cancelled'), 0) AS gross_today,
                    COALESCE((SELECT SUM(amount) FROM wallet_transactions WHERE transaction_type = 'platform_fee' AND DATE(occurred_at) = CURRENT_DATE), 0) AS fees_today,
                    COALESCE((SELECT SUM(amount) FROM payout_requests WHERE %s BETWEEN CURRENT_DATE AND %s AND status IN ('scheduled', 'processing')), 0) AS payouts_week,
                    COALESCE((SELECT COUNT(*) FROM wallet_transactions WHERE transaction_type IN ('adjustment', 'refund') AND status = 'under_review'), 0) AS pending_adjustments",
                $this->dateExpression('scheduled_for'),
                $this->currentDatePlusDays(7)
            )
        );

        return $statement->fetch() ?: [];
    }

    private function loadFinanceHighlights(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                COALESCE((SELECT COUNT(*) FROM payout_requests WHERE status IN ('scheduled', 'processing')), 0) AS active_payouts,
                COALESCE((SELECT SUM(amount) FROM payout_requests WHERE status IN ('scheduled', 'processing')), 0) AS active_payouts_amount,
                COALESCE((SELECT COUNT(*) FROM wallet_transactions WHERE transaction_type = 'adjustment' AND status = 'under_review'), 0) AS adjustments_count,
                COALESCE((SELECT COUNT(*) FROM wallet_transactions WHERE transaction_type = 'refund' AND status = 'under_review'), 0) AS refunds_count"
        );

        $row = $statement->fetch() ?: [];

        return [
            [
                'title' => 'Repasses em processamento',
                'text' => 'Lotes financeiros com conferencia concluida ou em etapa final antes do envio bancario.',
                'meta' => [
                    sprintf('%d lotes', (int) ($row['active_payouts'] ?? 0)),
                    $this->formatMoney($row['active_payouts_amount'] ?? 0),
                ],
                'action_label' => 'Abrir lote',
                'action_tone' => 'primary',
            ],
            [
                'title' => 'Ajustes e estornos',
                'text' => 'Eventos que precisam de revisao do financeiro antes do fechamento do ciclo da plataforma.',
                'meta' => [
                    sprintf('%d ajustes', (int) ($row['adjustments_count'] ?? 0)),
                    sprintf('%d estornos', (int) ($row['refunds_count'] ?? 0)),
                ],
                'action_label' => 'Revisar fila',
                'action_tone' => 'secondary',
            ],
        ];
    }

    private function loadPayoutRows(): array
    {
        $statement = $this->pdo->query(
            "SELECT s.trade_name, p.period_start, p.period_end, p.status, p.amount, p.note
             FROM payout_requests p
             INNER JOIN stores s ON s.id = p.store_id
             ORDER BY p.scheduled_for DESC
             LIMIT 12"
        );

        return array_map(fn (array $row) => [
            'partner' => $row['trade_name'],
            'period' => sprintf('%s a %s', $this->formatDate($row['period_start']), $this->formatDate($row['period_end'])),
            'status' => $this->normalizePayoutStatusLabel($row['status']),
            'status_type' => $this->normalizePayoutStatusType($row['status']),
            'net_amount' => $this->formatMoney($row['amount']),
            'note' => $row['note'] ?: 'sem observacoes',
        ], $statement->fetchAll() ?: []);
    }

    private function loadSupportQueue(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                st.id,
                st.scope,
                st.priority,
                st.status,
                st.subject,
                st.assigned_team,
                st.last_message_at,
                s.trade_name,
                u.full_name
             FROM support_tickets st
             LEFT JOIN stores s ON s.id = st.store_id
             LEFT JOIN driver_profiles dp ON dp.id = st.driver_profile_id
             LEFT JOIN users u ON u.id = dp.user_id
             ORDER BY
                CASE st.priority
                    WHEN 'critical' THEN 0
                    WHEN 'high' THEN 1
                    ELSE 2
                END,
                st.last_message_at DESC
             LIMIT 12"
        );

        return array_map(fn (array $row) => [
            'id' => '#SUP-' . strtoupper(substr(str_replace('-', '', (string) $row['id']), 0, 6)),
            'ticket_id' => $row['id'],
            'title' => sprintf(
                '#SUP-%s - %s',
                strtoupper(substr(str_replace('-', '', (string) $row['id']), 0, 6)),
                $row['scope'] === 'partner' ? ($row['trade_name'] ?: 'Parceiro') : ($row['full_name'] ?: 'Entregador')
            ),
            'summary' => $row['subject'],
            'status' => $this->normalizeSupportPriorityLabel((string) $row['priority']),
            'statusType' => $this->normalizeSupportPriorityType((string) $row['priority']),
        ], $statement->fetchAll() ?: []);
    }

    private function findSupportTicket(string $ticketId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT
                st.id,
                st.scope,
                st.channel,
                st.priority,
                st.status,
                st.subject,
                st.description,
                st.assigned_team,
                st.created_at,
                st.last_message_at,
                s.trade_name,
                u.full_name
             FROM support_tickets st
             LEFT JOIN stores s ON s.id = st.store_id
             LEFT JOIN driver_profiles dp ON dp.id = st.driver_profile_id
             LEFT JOIN users u ON u.id = dp.user_id
             WHERE st.id = :ticket_id
             LIMIT 1"
        );
        $statement->execute([
            'ticket_id' => $ticketId,
        ]);

        return $statement->fetch();
    }

    private function loadSupportThreadMessages(string $ticketId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT sm.id, sm.sender_role, sm.body, sm.created_at, u.full_name
             FROM support_messages sm
             LEFT JOIN users u ON u.id = sm.sender_user_id
             WHERE sm.ticket_id = :ticket_id
             ORDER BY sm.created_at ASC"
        );
        $statement->execute([
            'ticket_id' => $ticketId,
        ]);

        return array_map(function (array $row): array {
            $isAdmin = (string) $row['sender_role'] === 'admin';

            return [
                'id' => $row['id'],
                'direction' => $isAdmin ? 'outgoing' : 'incoming',
                'author' => $isAdmin
                    ? ($row['full_name'] ?: 'Fox Platform')
                    : ($row['full_name'] ?: 'Parceiro/Entregador'),
                'body' => $row['body'],
                'time' => $this->formatDateTime((string) $row['created_at']),
                'role' => $row['sender_role'],
            ];
        }, $statement->fetchAll() ?: []);
    }

    private function loadSupportDistribution(): array
    {
        $statement = $this->pdo->query(
            "SELECT assigned_team, COUNT(*) AS total
             FROM support_tickets
             GROUP BY assigned_team
             ORDER BY total DESC, assigned_team ASC"
        );

        return array_map(fn (array $row) => [
            'label' => ucfirst((string) ($row['assigned_team'] ?: 'operacao')),
            'value' => sprintf('%d tickets', (int) $row['total']),
        ], $statement->fetchAll() ?: []);
    }

    private function loadSupportSla(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                SUM(CASE WHEN status IN ('open', 'in_progress') THEN 1 ELSE 0 END) AS active_queue,
                SUM(CASE WHEN priority = 'critical' THEN 1 ELSE 0 END) AS critical_queue,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved_queue
             FROM support_tickets"
        );
        $row = $statement->fetch() ?: [];

        return [
            sprintf('%d protocolos na fila ativa.', (int) ($row['active_queue'] ?? 0)),
            sprintf('%d chamados em prioridade critica.', (int) ($row['critical_queue'] ?? 0)),
            sprintf('%d chamados ja concluídos neste ciclo.', (int) ($row['resolved_queue'] ?? 0)),
        ];
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
            'description' => $row['note'] ?: 'Atualizacao operacional registrada no admin.',
            'actor' => $row['actor_name'] ?: 'Fox Platform',
            'created_at' => $this->formatDateTime((string) $row['created_at']),
        ], $statement->fetchAll() ?: []);
    }

    private function formatOrderRow(array $row): array
    {
        [$statusLabel, $statusType] = $this->normalizeOrderStatusMeta((string) $row['status']);

        return [
            'id' => '#' . $row['order_number'],
            'order_id' => $row['id'],
            'store_name' => $row['store_name'],
            'customer' => $row['customer_name'],
            'status' => $statusLabel,
            'status_key' => $row['status'],
            'statusType' => $statusType,
            'sla' => $row['status'] === 'completed'
                ? 'entregue'
                : ($row['status'] === 'cancelled' ? 'fora da janela' : sprintf('%d min', max(1, (int) floor((time() - strtotime($row['placed_at'])) / 60)))),
            'driver_name' => $row['driver_name'],
            'value' => $this->formatMoney($row['total']),
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
            'sla' => $row['status'] === 'completed'
                ? 'entregue'
                : ($row['status'] === 'cancelled' ? 'fora da janela' : sprintf('%d min', max(1, (int) floor((time() - strtotime($row['placed_at'])) / 60)))),
        ];
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

    private function normalizePayoutStatusLabel(string $status): string
    {
        return match ($status) {
            'scheduled' => 'agendado',
            'processing' => 'em conferencia',
            'completed' => 'aprovado',
            'blocked' => 'bloqueado',
            default => 'em analise',
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

    private function normalizeSupportPriorityLabel(string $priority): string
    {
        return match ($priority) {
            'critical' => 'critico',
            'high' => 'alta',
            default => 'normal',
        };
    }

    private function normalizeSupportPriorityType(string $priority): string
    {
        return match ($priority) {
            'critical' => 'danger',
            'high' => 'warning',
            default => 'success',
        };
    }

    private function normalizeSupportChannelLabel(string $channel): string
    {
        return match ($channel) {
            'operations' => 'Operacao',
            'finance' => 'Financeiro',
            'catalog' => 'Catalogo',
            'documents' => 'Documentos',
            'earnings' => 'Ganhos',
            'commercial' => 'Comercial',
            default => ucfirst($channel),
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
            'answered' => 'success',
            'in_progress' => 'warning',
            default => 'warning',
        };
    }

    private function formatSupportTicketDetail(array $row): array
    {
        $scopeLabel = $row['scope'] === 'partner' ? 'Parceiro' : 'Entregador';
        $counterpart = $row['scope'] === 'partner'
            ? ($row['trade_name'] ?: 'Parceiro sem loja')
            : ($row['full_name'] ?: 'Entregador sem nome');

        return [
            'id' => '#SUP-' . strtoupper(substr(str_replace('-', '', (string) $row['id']), 0, 6)),
            'ticket_id' => $row['id'],
            'scope' => $scopeLabel,
            'counterpart' => $counterpart,
            'channel' => $this->normalizeSupportChannelLabel((string) $row['channel']),
            'priority' => $this->normalizeSupportPriorityLabel((string) $row['priority']),
            'priority_type' => $this->normalizeSupportPriorityType((string) $row['priority']),
            'status' => $this->normalizeSupportStatusLabel((string) $row['status']),
            'status_type' => $this->normalizeSupportStatusType((string) $row['status']),
            'assigned_team' => ucfirst((string) ($row['assigned_team'] ?: 'operacao')),
            'subject' => $row['subject'],
            'description' => $row['description'],
            'created_at' => $this->formatDateTime((string) $row['created_at']),
            'last_message_at' => $this->formatDateTime((string) $row['last_message_at']),
        ];
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

    private function formatMoney(float|int|string|null $value): string
    {
        return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
