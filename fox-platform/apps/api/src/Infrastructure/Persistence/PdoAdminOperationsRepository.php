<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoAdminOperationsRepository implements AdminOperationsRepository
{
    use SupportsSqlDialect;

    private const DEFAULT_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

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

    public function updateOrderStatus(string $userId, string $orderId, array $data): array
    {
        $current = $this->findOrderRecord($orderId);

        if (!$current) {
            throw new ApiException(404, 'ORDER_NOT_FOUND', 'Pedido nao encontrado para a operacao.');
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
                $cancelledAt = null;
            }

            if ($data['status'] === 'cancelled') {
                $cancelledAt = date(DATE_ATOM);
            }

            if ($data['status'] !== 'cancelled') {
                $cancelledAt = null;
            }

            $update = $this->pdo->prepare(
                "UPDATE orders
                 SET status = :status,
                     accepted_at = :accepted_at,
                     completed_at = :completed_at,
                     cancelled_at = :cancelled_at,
                     updated_at = NOW()
                 WHERE id = :order_id"
            );
            $update->execute([
                'status' => $data['status'],
                'accepted_at' => $acceptedAt,
                'completed_at' => $completedAt,
                'cancelled_at' => $cancelledAt,
                'order_id' => $orderId,
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
                'note' => $data['note'] !== '' ? $data['note'] : 'Atualizacao manual via Admin Fox Platform',
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getOrderDetail($orderId);
    }

    public function addOrderNote(string $userId, string $orderId, array $data): array
    {
        $current = $this->findOrderRecord($orderId);

        if (!$current) {
            throw new ApiException(404, 'ORDER_NOT_FOUND', 'Pedido nao encontrado para a operacao.');
        }

        $this->pdo->beginTransaction();

        try {
            $touch = $this->pdo->prepare(
                "UPDATE orders
                 SET updated_at = NOW()
                 WHERE id = :order_id"
            );
            $touch->execute([
                'order_id' => $orderId,
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
                'next_status' => $current['status'],
                'actor_user_id' => $userId,
                'note' => $data['note'],
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getOrderDetail($orderId);
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

    public function getAnalytics(): array
    {
        $platform = $this->loadPlatformMetrics();
        $finance = $this->loadFinanceMetrics();

        return [
            'cards' => [
                ['label' => 'volume bruto no mes', 'value' => $this->formatMoney($this->loadCurrentMonthGross())],
                ['label' => 'pedidos concluidos', 'value' => (string) $this->loadCompletedOrdersCount()],
                ['label' => 'pedidos dentro do SLA', 'value' => $this->loadSlaRate()],
                ['label' => 'comissoes no dia', 'value' => $this->formatMoney($finance['fees_today'] ?? 0)],
            ],
            'status_distribution' => $this->loadOrderStatusDistribution(),
            'city_distribution' => $this->loadStoreCityDistribution(),
            'highlights' => [
                sprintf('%d parceiros ativos sustentam a operacao atual.', (int) ($platform['active_partners'] ?? 0)),
                sprintf('%d entregadores estao habilitados para a malha operacional.', (int) ($platform['active_drivers'] ?? 0)),
                sprintf('%d pedidos seguem em andamento neste momento.', (int) ($platform['in_progress_orders'] ?? 0)),
            ],
        ];
    }

    public function getReports(): array
    {
        $platform = $this->loadPlatformMetrics();

        return [
            'summary' => [
                ['label' => 'parceiros ativos', 'value' => (string) ($platform['active_partners'] ?? 0)],
                ['label' => 'entregadores ativos', 'value' => (string) ($platform['active_drivers'] ?? 0)],
                ['label' => 'tickets em aberto', 'value' => (string) $this->loadOpenSupportCount()],
                ['label' => 'repasses em processamento', 'value' => (string) $this->loadProcessingPayoutCount()],
            ],
            'partner_status' => $this->loadStoreStatusReport(),
            'driver_status' => $this->loadDriverStatusReport(),
            'support_teams' => $this->loadSupportDistribution(),
            'top_stores' => $this->loadTopStoresReport(),
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

    public function getPartnerApprovalDetail(string $partnerId): array
    {
        $partner = $this->findPartnerApproval($partnerId);

        if (!$partner) {
            throw new ApiException(404, 'PARTNER_APPROVAL_NOT_FOUND', 'Parceiro nao encontrado para analise.');
        }

        return [
            'approval' => $this->formatPartnerApprovalDetail($partner),
            'documents' => $this->loadPartnerApprovalDocuments($partnerId),
            'review_history' => $this->loadApprovalReviewHistory('partner', $partnerId),
        ];
    }

    public function reviewPartnerApproval(string $userId, string $partnerId, string $decision): array
    {
        $this->applyPartnerApprovalDecision($userId, $partnerId, $decision, '');

        return $this->getPartnerApprovals();
    }

    public function resolvePartnerApproval(string $userId, string $partnerId, array $data): array
    {
        $this->applyPartnerApprovalDecision($userId, $partnerId, $data['decision'], $data['note'] ?? '');

        return $this->getPartnerApprovalDetail($partnerId);
    }

    private function applyPartnerApprovalDecision(string $userId, string $partnerId, string $decision, string $note): void
    {
        $partner = $this->findPartnerApproval($partnerId);

        if (!$partner) {
            throw new ApiException(404, 'PARTNER_APPROVAL_NOT_FOUND', 'Parceiro nao encontrado para analise.');
        }

        $nextStatus = $decision === 'approve' ? 'active' : 'rejected';

        $this->pdo->beginTransaction();

        try {
            $storeUpdate = $this->pdo->prepare(
                "UPDATE stores
                 SET status = :status,
                     updated_at = NOW()
                 WHERE id = :partner_id"
            );
            $storeUpdate->execute([
                'status' => $nextStatus,
                'partner_id' => $partnerId,
            ]);

            $accountUpdate = $this->pdo->prepare(
                "UPDATE partner_accounts
                 SET status = :status,
                     updated_at = NOW()
                 WHERE id = :partner_account_id"
            );
            $accountUpdate->execute([
                'status' => $nextStatus,
                'partner_account_id' => $partner['partner_account_id'],
            ]);

            $this->recordApprovalReview(
                'partner',
                $partnerId,
                $decision,
                $note !== '' ? $note : ($decision === 'approve'
                    ? 'Cadastro aprovado pela operacao administrativa.'
                    : 'Cadastro movido para revisao manual pela operacao administrativa.'),
                $userId
            );

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
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

    public function getAccess(): array
    {
        $members = $this->loadAdminMembers();
        $roles = $this->loadAdminRoles();

        return [
            'summary' => [
                ['label' => 'usuarios internos', 'value' => (string) count($members)],
                ['label' => 'ativos', 'value' => (string) count(array_filter($members, static fn (array $member) => $member['status_key'] === 'active'))],
                ['label' => 'super admins', 'value' => (string) count(array_filter($members, static fn (array $member) => (bool) ($member['is_super'] ?? false)))],
                ['label' => 'pendentes ou suspensos', 'value' => (string) count(array_filter($members, static fn (array $member) => in_array($member['status_key'], ['pending', 'suspended', 'blocked'], true)))],
            ],
            'roles' => $roles,
            'members' => $members,
            'allowed_roles' => array_map(
                static fn (array $role): array => [
                    'slug' => $role['slug'],
                    'label' => $role['name'],
                ],
                $roles
            ),
        ];
    }

    public function createAccessMember(string $userId, array $data): array
    {
        $emailStatement = $this->pdo->prepare(
            'SELECT id FROM users WHERE LOWER(email) = LOWER(:email) AND deleted_at IS NULL LIMIT 1'
        );
        $emailStatement->execute(['email' => $data['email']]);
        if ($emailStatement->fetch()) {
            throw new ApiException(409, 'ADMIN_MEMBER_EMAIL_EXISTS', 'Ja existe um usuario com este e-mail.');
        }

        $role = $this->findAdminRoleBySlug($data['role_slug']);

        if (!$role) {
            throw new ApiException(404, 'ADMIN_ROLE_NOT_FOUND', 'Perfil administrativo nao encontrado.');
        }

        $memberId = $this->newUuid();

        $this->pdo->beginTransaction();

        try {
            $userInsert = $this->pdo->prepare(
                "INSERT INTO users (
                    id, full_name, email, phone, password_hash, status, locale
                 ) VALUES (
                    :id, :full_name, :email, :phone, :password_hash, :status, 'pt_BR'
                 )"
            );
            $userInsert->execute([
                'id' => $memberId,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password_hash' => self::DEFAULT_PASSWORD_HASH,
                'status' => $data['status'],
            ]);

            $profileInsert = $this->pdo->prepare(
                sprintf(
                    "INSERT INTO admin_profiles (
                        id, user_id, department, is_super
                     ) VALUES (
                        %s, :user_id, :department, :is_super
                     )",
                    $this->uuidExpression()
                )
            );
            $profileInsert->execute([
                'user_id' => $memberId,
                'department' => $data['department'],
                'is_super' => $data['role_slug'] === 'super_admin' ? 1 : 0,
            ]);

            $this->replaceAdminRole($memberId, $role['id']);
            $this->createAdminWelcomeNotification($memberId, $data['role_slug']);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getAccess();
    }

    public function updateAccessMember(string $userId, string $memberId, array $data): array
    {
        $member = $this->findAdminMemberRecord($memberId);

        if (!$member) {
            throw new ApiException(404, 'ADMIN_MEMBER_NOT_FOUND', 'Membro administrativo nao encontrado.');
        }

        $emailStatement = $this->pdo->prepare(
            'SELECT id FROM users WHERE LOWER(email) = LOWER(:email) AND id <> :user_id AND deleted_at IS NULL LIMIT 1'
        );
        $emailStatement->execute([
            'email' => $data['email'],
            'user_id' => $memberId,
        ]);
        if ($emailStatement->fetch()) {
            throw new ApiException(409, 'ADMIN_MEMBER_EMAIL_EXISTS', 'Ja existe um usuario com este e-mail.');
        }

        $role = $this->findAdminRoleBySlug($data['role_slug']);

        if (!$role) {
            throw new ApiException(404, 'ADMIN_ROLE_NOT_FOUND', 'Perfil administrativo nao encontrado.');
        }

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
                'email' => $data['email'],
                'phone' => $data['phone'],
                'user_id' => $memberId,
            ]);

            $profileUpdate = $this->pdo->prepare(
                "UPDATE admin_profiles
                 SET department = :department,
                     is_super = :is_super,
                     updated_at = NOW()
                 WHERE user_id = :user_id"
            );
            $profileUpdate->execute([
                'department' => $data['department'],
                'is_super' => $data['role_slug'] === 'super_admin' ? 1 : 0,
                'user_id' => $memberId,
            ]);

            $this->replaceAdminRole($memberId, $role['id']);

            if ($member['status'] !== $data['status']) {
                $statusUpdate = $this->pdo->prepare(
                    "UPDATE users
                     SET status = :status,
                         updated_at = NOW()
                     WHERE id = :user_id"
                );
                $statusUpdate->execute([
                    'status' => $data['status'],
                    'user_id' => $memberId,
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getAccess();
    }

    public function updateAccessMemberStatus(string $userId, string $memberId, array $data): array
    {
        $member = $this->findAdminMemberRecord($memberId);

        if (!$member) {
            throw new ApiException(404, 'ADMIN_MEMBER_NOT_FOUND', 'Membro administrativo nao encontrado.');
        }

        $statement = $this->pdo->prepare(
            "UPDATE users
             SET status = :status,
                 updated_at = NOW()
             WHERE id = :user_id"
        );
        $statement->execute([
            'status' => $data['status'],
            'user_id' => $memberId,
        ]);

        return $this->getAccess();
    }

    public function getNotifications(string $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, level, context, title, body, action_label, action_url, is_read, created_at
             FROM notifications
             WHERE scope = 'admin' AND user_id = :user_id
             ORDER BY is_read ASC, created_at DESC
             LIMIT 40"
        );
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll() ?: [];

        return [
            'summary' => [
                ['label' => 'nao lidas', 'value' => (string) count(array_filter($rows, static fn (array $row) => !(bool) $row['is_read']))],
                ['label' => 'criticas ou altas', 'value' => (string) count(array_filter($rows, static fn (array $row) => in_array($row['level'], ['warning', 'danger'], true)))],
                ['label' => 'total recente', 'value' => (string) count($rows)],
            ],
            'items' => array_map(fn (array $row) => $this->formatAdminNotificationRow($row), $rows),
        ];
    }

    public function markNotificationRead(string $userId, string $notificationId): array
    {
        $statement = $this->pdo->prepare(
            "UPDATE notifications
             SET is_read = TRUE,
                 read_at = NOW()
             WHERE id = :notification_id
               AND scope = 'admin'
               AND user_id = :user_id"
        );
        $statement->execute([
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);

        if ($statement->rowCount() === 0) {
            throw new ApiException(404, 'NOTIFICATION_NOT_FOUND', 'Notificacao nao encontrada para esta conta administrativa.');
        }

        return $this->getNotifications($userId);
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

    public function getDriverApprovalDetail(string $driverId): array
    {
        $driver = $this->findDriverApproval($driverId);

        if (!$driver) {
            throw new ApiException(404, 'DRIVER_APPROVAL_NOT_FOUND', 'Entregador nao encontrado para analise.');
        }

        return [
            'approval' => $this->formatDriverApprovalDetail($driver),
            'documents' => $this->loadDriverApprovalDocuments($driverId),
            'review_history' => $this->loadApprovalReviewHistory('driver', $driverId),
        ];
    }

    public function reviewDriverApproval(string $userId, string $driverId, string $decision): array
    {
        $this->applyDriverApprovalDecision($userId, $driverId, $decision, '');

        return $this->getDriverApprovals();
    }

    public function resolveDriverApproval(string $userId, string $driverId, array $data): array
    {
        $this->applyDriverApprovalDecision($userId, $driverId, $data['decision'], $data['note'] ?? '');

        return $this->getDriverApprovalDetail($driverId);
    }

    private function applyDriverApprovalDecision(string $userId, string $driverId, string $decision, string $note): void
    {
        $driver = $this->findDriverApproval($driverId);

        if (!$driver) {
            throw new ApiException(404, 'DRIVER_APPROVAL_NOT_FOUND', 'Entregador nao encontrado para analise.');
        }

        $nextStatus = $decision === 'approve' ? 'active' : 'rejected';

        $this->pdo->beginTransaction();

        try {
            $profileUpdate = $this->pdo->prepare(
                "UPDATE driver_profiles
                 SET status = :status,
                     updated_at = NOW()
                 WHERE id = :driver_id"
            );
            $profileUpdate->execute([
                'status' => $nextStatus,
                'driver_id' => $driverId,
            ]);

            $userUpdate = $this->pdo->prepare(
                "UPDATE users
                 SET status = :status,
                     updated_at = NOW()
                 WHERE id = :user_id"
            );
            $userUpdate->execute([
                'status' => $nextStatus === 'active' ? 'active' : 'pending',
                'user_id' => $driver['user_id'],
            ]);

            $this->recordApprovalReview(
                'driver',
                $driverId,
                $decision,
                $note !== '' ? $note : ($decision === 'approve'
                    ? 'Cadastro aprovado pela operacao administrativa.'
                    : 'Cadastro movido para revisao manual pela operacao administrativa.'),
                $userId
            );

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
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

    private function loadCurrentMonthGross(): float
    {
        $statement = $this->pdo->query(
            "SELECT COALESCE(SUM(total), 0) AS gross_month
             FROM orders
             WHERE status <> 'cancelled'
               AND YEAR(placed_at) = YEAR(CURRENT_DATE)
               AND MONTH(placed_at) = MONTH(CURRENT_DATE)"
        );

        return (float) (($statement->fetch()['gross_month'] ?? 0));
    }

    private function loadCompletedOrdersCount(): int
    {
        $statement = $this->pdo->query(
            "SELECT COUNT(*) AS total
             FROM orders
             WHERE status = 'completed'"
        );

        return (int) (($statement->fetch()['total'] ?? 0));
    }

    private function loadSlaRate(): string
    {
        $statement = $this->pdo->query(
            "SELECT
                SUM(CASE WHEN status IN ('accepted', 'preparing', 'ready_for_pickup', 'on_route', 'completed') THEN 1 ELSE 0 END) AS inside_sla,
                COUNT(*) AS total
             FROM orders"
        );
        $row = $statement->fetch() ?: ['inside_sla' => 0, 'total' => 0];

        $total = (int) ($row['total'] ?? 0);
        if ($total === 0) {
            return '0%';
        }

        $ratio = ((int) ($row['inside_sla'] ?? 0)) / $total * 100;

        return number_format($ratio, 1, ',', '.') . '%';
    }

    private function loadOrderStatusDistribution(): array
    {
        $statement = $this->pdo->query(
            "SELECT status, COUNT(*) AS total
             FROM orders
             GROUP BY status
             ORDER BY total DESC, status ASC"
        );
        $rows = $statement->fetchAll() ?: [];
        $total = array_sum(array_map(static fn (array $row): int => (int) $row['total'], $rows));

        if ($total === 0) {
            return [];
        }

        return array_map(function (array $row) use ($total): array {
            $count = (int) $row['total'];
            return [
                'label' => $this->normalizeOrderStatusLabel((string) $row['status']),
                'value' => $count,
                'share' => round(($count / $total) * 100, 1),
            ];
        }, $rows);
    }

    private function loadStoreCityDistribution(): array
    {
        $statement = $this->pdo->query(
            "SELECT COALESCE(city, 'Sem cidade') AS city, COUNT(*) AS total
             FROM stores
             GROUP BY COALESCE(city, 'Sem cidade')
             ORDER BY total DESC, city ASC
             LIMIT 5"
        );
        $rows = $statement->fetchAll() ?: [];
        $total = array_sum(array_map(static fn (array $row): int => (int) $row['total'], $rows));

        if ($total === 0) {
            return [];
        }

        return array_map(static function (array $row) use ($total): array {
            $count = (int) $row['total'];
            return [
                'label' => $row['city'],
                'value' => $count,
                'share' => round(($count / $total) * 100, 1),
            ];
        }, $rows);
    }

    private function loadOpenSupportCount(): int
    {
        $statement = $this->pdo->query(
            "SELECT COUNT(*) AS total
             FROM support_tickets
             WHERE status IN ('open', 'in_progress')"
        );

        return (int) (($statement->fetch()['total'] ?? 0));
    }

    private function loadProcessingPayoutCount(): int
    {
        $statement = $this->pdo->query(
            "SELECT COUNT(*) AS total
             FROM payout_requests
             WHERE status IN ('scheduled', 'processing')"
        );

        return (int) (($statement->fetch()['total'] ?? 0));
    }

    private function loadStoreStatusReport(): array
    {
        $statement = $this->pdo->query(
            "SELECT status, COUNT(*) AS total
             FROM stores
             GROUP BY status
             ORDER BY total DESC, status ASC"
        );

        return array_map(function (array $row): array {
            return [
                'label' => $this->normalizeStoreStatusLabel((string) $row['status']),
                'value' => (string) $row['total'],
            ];
        }, $statement->fetchAll() ?: []);
    }

    private function loadDriverStatusReport(): array
    {
        $statement = $this->pdo->query(
            "SELECT status, COUNT(*) AS total
             FROM driver_profiles
             GROUP BY status
             ORDER BY total DESC, status ASC"
        );

        return array_map(function (array $row): array {
            return [
                'label' => $this->normalizeDriverStatusLabel((string) $row['status']),
                'value' => (string) $row['total'],
            ];
        }, $statement->fetchAll() ?: []);
    }

    private function loadTopStoresReport(): array
    {
        $statement = $this->pdo->query(
            "SELECT s.trade_name, COUNT(o.id) AS total_orders, COALESCE(SUM(o.total), 0) AS gross_total
             FROM stores s
             LEFT JOIN orders o ON o.store_id = s.id
             GROUP BY s.id, s.trade_name
             ORDER BY total_orders DESC, gross_total DESC, s.trade_name ASC
             LIMIT 6"
        );

        return array_map(function (array $row): array {
            return [
                'label' => $row['trade_name'],
                'orders' => (string) $row['total_orders'],
                'gross' => $this->formatMoney($row['gross_total']),
            ];
        }, $statement->fetchAll() ?: []);
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

    private function loadAdminMembers(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                u.id AS user_id,
                u.full_name,
                u.email,
                u.phone,
                u.status,
                u.last_login_at,
                u.created_at,
                ap.department,
                ap.is_super,
                r.slug AS role_slug,
                r.name AS role_name
             FROM admin_profiles ap
             INNER JOIN users u ON u.id = ap.user_id
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id AND r.scope = 'admin'
             WHERE u.deleted_at IS NULL
             ORDER BY ap.is_super DESC, u.created_at DESC, u.full_name ASC"
        );

        $permissionMap = $this->loadAdminRolePermissionMap();

        return array_map(function (array $row) use ($permissionMap): array {
            $roleSlug = (string) ($row['role_slug'] ?: 'super_admin');
            $permissions = $permissionMap[$roleSlug]['names'] ?? [];

            return [
                'id' => $row['user_id'],
                'user_id' => $row['user_id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'phone' => $row['phone'] ?: '-',
                'department' => $row['department'] ?: 'Operacao',
                'role_slug' => $roleSlug,
                'role_label' => $row['role_name'] ?: 'Super Admin',
                'permissions' => $permissions,
                'status' => $this->normalizeUserStatusLabel((string) $row['status']),
                'status_key' => (string) $row['status'],
                'status_type' => $this->normalizeUserStatusType((string) $row['status']),
                'is_super' => (bool) ($row['is_super'] ?? false),
                'last_login_at' => $row['last_login_at'] ? $this->formatDateTime((string) $row['last_login_at']) : '-',
                'created_at' => $this->formatDateTime((string) $row['created_at']),
            ];
        }, $statement->fetchAll() ?: []);
    }

    private function loadAdminRoles(): array
    {
        $statement = $this->pdo->query(
            "SELECT
                r.id,
                r.slug,
                r.name,
                r.description,
                p.slug AS permission_slug,
                p.name AS permission_name
             FROM roles r
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             LEFT JOIN permissions p ON p.id = rp.permission_id
             WHERE r.scope = 'admin'
             ORDER BY r.name ASC, p.name ASC"
        );

        $roles = [];

        foreach ($statement->fetchAll() ?: [] as $row) {
            $slug = (string) $row['slug'];

            if (!isset($roles[$slug])) {
                $roles[$slug] = [
                    'id' => $row['id'],
                    'slug' => $slug,
                    'name' => $row['name'],
                    'description' => $row['description'] ?: '',
                    'permissions' => [],
                    'permission_slugs' => [],
                ];
            }

            if (!empty($row['permission_slug'])) {
                $roles[$slug]['permissions'][] = $row['permission_name'];
                $roles[$slug]['permission_slugs'][] = $row['permission_slug'];
            }
        }

        return array_values($roles);
    }

    private function loadAdminRolePermissionMap(): array
    {
        $statement = $this->pdo->query(
            "SELECT r.slug AS role_slug, p.slug AS permission_slug, p.name AS permission_name
             FROM roles r
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             LEFT JOIN permissions p ON p.id = rp.permission_id
             WHERE r.scope = 'admin'
             ORDER BY r.slug ASC, p.name ASC"
        );

        $map = [];

        foreach ($statement->fetchAll() ?: [] as $row) {
            $roleSlug = (string) $row['role_slug'];

            if (!isset($map[$roleSlug])) {
                $map[$roleSlug] = [
                    'slugs' => [],
                    'names' => [],
                ];
            }

            if (!empty($row['permission_slug'])) {
                $map[$roleSlug]['slugs'][] = $row['permission_slug'];
                $map[$roleSlug]['names'][] = $row['permission_name'];
            }
        }

        return $map;
    }

    private function findAdminRoleBySlug(string $roleSlug): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT id, slug, name
             FROM roles
             WHERE slug = :slug
               AND scope = 'admin'
             LIMIT 1"
        );
        $statement->execute(['slug' => $roleSlug]);

        return $statement->fetch();
    }

    private function findAdminMemberRecord(string $memberId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT
                u.id,
                u.full_name,
                u.email,
                u.phone,
                u.status,
                ap.department,
                ap.is_super,
                r.slug AS role_slug,
                r.name AS role_name
             FROM admin_profiles ap
             INNER JOIN users u ON u.id = ap.user_id
             LEFT JOIN user_roles ur ON ur.user_id = u.id
             LEFT JOIN roles r ON r.id = ur.role_id AND r.scope = 'admin'
             WHERE u.id = :member_id
               AND u.deleted_at IS NULL
             LIMIT 1"
        );
        $statement->execute(['member_id' => $memberId]);

        return $statement->fetch();
    }

    private function replaceAdminRole(string $userId, string $roleId): void
    {
        $deleteStatement = $this->pdo->prepare(
            "DELETE FROM user_roles
             WHERE user_id = :user_id
               AND role_id IN (
                   SELECT id FROM roles WHERE scope = 'admin'
               )"
        );
        $deleteStatement->execute(['user_id' => $userId]);

        $insertStatement = $this->pdo->prepare(
            sprintf(
                "INSERT INTO user_roles (id, user_id, role_id, scope_type, scope_id)
                 VALUES (%s, :user_id, :role_id, NULL, NULL)",
                $this->uuidExpression()
            )
        );
        $insertStatement->execute([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    private function createAdminWelcomeNotification(string $userId, string $roleSlug): void
    {
        $title = match ($roleSlug) {
            'admin_financeiro' => 'Acesso financeiro criado',
            'admin_comercial' => 'Acesso comercial criado',
            'suporte' => 'Acesso de suporte criado',
            'admin_operacional' => 'Acesso operacional criado',
            default => 'Acesso administrativo criado',
        };

        $body = match ($roleSlug) {
            'admin_financeiro' => 'O perfil financeiro foi habilitado com acesso aos modulos financeiros da Fox Platform.',
            'admin_comercial' => 'O perfil comercial foi habilitado para apoiar aprovacoes e jornadas de parceiros.',
            'suporte' => 'O perfil de suporte foi habilitado para atuar na fila de atendimento da plataforma.',
            'admin_operacional' => 'O perfil operacional foi habilitado para pedidos, fila de aprovacao e acompanhamento da operacao.',
            default => 'O novo membro administrativo recebeu acesso inicial ao ecossistema interno da Fox Platform.',
        };

        $statement = $this->pdo->prepare(
            sprintf(
                "INSERT INTO notifications (
                    id, scope, user_id, level, context, title, body, action_label, action_url, is_read, created_at
                 ) VALUES (
                    %s, 'admin', :user_id, 'info', 'access', :title, :body, :action_label, :action_url, FALSE, NOW()
                 )",
                $this->uuidExpression()
            )
        );
        $statement->execute([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'action_label' => 'Abrir painel',
            'action_url' => './index.html',
        ]);
    }

    private function formatAdminNotificationRow(array $row): array
    {
        $level = (string) ($row['level'] ?? 'info');

        return [
            'id' => $row['id'],
            'context' => ucfirst((string) ($row['context'] ?: 'plataforma')),
            'title' => $row['title'],
            'body' => $row['body'],
            'action_label' => $row['action_label'] ?: 'Abrir',
            'action_url' => $row['action_url'] ?: '',
            'is_read' => (bool) ($row['is_read'] ?? false),
            'created_at' => $this->formatDateTime((string) $row['created_at']),
            'level' => $this->normalizeNotificationLevelLabel($level),
            'level_type' => $this->normalizeNotificationLevelType($level),
        ];
    }

    private function normalizeUserStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'ativo',
            'suspended' => 'suspenso',
            'blocked' => 'bloqueado',
            default => 'pendente',
        };
    }

    private function normalizeUserStatusType(string $status): string
    {
        return match ($status) {
            'active' => 'success',
            'blocked' => 'danger',
            'suspended' => 'warning',
            default => 'warning',
        };
    }

    private function normalizeNotificationLevelLabel(string $level): string
    {
        return match ($level) {
            'success' => 'confirmada',
            'warning' => 'atencao',
            'danger' => 'critica',
            default => 'informativa',
        };
    }

    private function normalizeNotificationLevelType(string $level): string
    {
        return match ($level) {
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            default => 'warning',
        };
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

    private function findPartnerApproval(string $partnerId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT
                s.id,
                s.partner_account_id,
                s.trade_name,
                s.legal_name,
                s.document_number,
                s.email,
                s.phone,
                s.status,
                s.city,
                s.state,
                ap.status AS account_status,
                u.full_name AS owner_name,
                u.email AS owner_email,
                u.phone AS owner_phone
             FROM stores s
             INNER JOIN partner_accounts ap ON ap.id = s.partner_account_id
             INNER JOIN users u ON u.id = ap.owner_user_id
             WHERE s.id = :partner_id
             LIMIT 1"
        );
        $statement->execute([
            'partner_id' => $partnerId,
        ]);

        return $statement->fetch();
    }

    private function loadPartnerApprovalDocuments(string $partnerId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT document_type, label, file_name, status, metadata, updated_at
             FROM store_documents
             WHERE store_id = :partner_id
             ORDER BY updated_at DESC, created_at DESC"
        );
        $statement->execute([
            'partner_id' => $partnerId,
        ]);

        return array_map(fn (array $row) => [
            'label' => $row['label'],
            'type' => $row['document_type'],
            'file_name' => $row['file_name'] ?: '-',
            'status' => $this->normalizeDocumentStatusLabel((string) $row['status']),
            'status_type' => $this->normalizeDocumentStatusType((string) $row['status']),
            'meta' => $this->formatDocumentMetadata($row['metadata'] ?? null),
            'updated_at' => $this->formatDateTime((string) $row['updated_at']),
        ], $statement->fetchAll() ?: []);
    }

    private function findDriverApproval(string $driverId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT
                dp.id,
                dp.user_id,
                dp.modal,
                dp.status,
                dp.city,
                dp.state,
                dp.bank_name,
                dp.bank_branch_number,
                dp.bank_account_number,
                dp.rating,
                dp.last_active_at,
                u.full_name,
                u.email,
                u.phone
             FROM driver_profiles dp
             INNER JOIN users u ON u.id = dp.user_id
             WHERE dp.id = :driver_id
             LIMIT 1"
        );
        $statement->execute([
            'driver_id' => $driverId,
        ]);

        return $statement->fetch();
    }

    private function loadDriverApprovalDocuments(string $driverId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT document_type, label, file_name, status, created_at
             FROM driver_documents
             WHERE driver_profile_id = :driver_id
             ORDER BY created_at DESC"
        );
        $statement->execute([
            'driver_id' => $driverId,
        ]);

        return array_map(fn (array $row) => [
            'label' => $row['label'],
            'type' => $row['document_type'],
            'file_name' => $row['file_name'],
            'status' => $this->normalizeDocumentStatusLabel((string) $row['status']),
            'status_type' => $this->normalizeDocumentStatusType((string) $row['status']),
            'meta' => '-',
            'updated_at' => $this->formatDateTime((string) $row['created_at']),
        ], $statement->fetchAll() ?: []);
    }

    private function loadApprovalReviewHistory(string $entityType, string $entityId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT ar.decision, ar.note, ar.created_at, u.full_name
             FROM approval_reviews ar
             LEFT JOIN users u ON u.id = ar.actor_user_id
             WHERE ar.entity_type = :entity_type
               AND ar.entity_id = :entity_id
             ORDER BY ar.created_at DESC"
        );
        $statement->execute([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);

        return array_map(fn (array $row) => [
            'title' => $this->normalizeApprovalDecisionTitle((string) $row['decision']),
            'description' => $row['note'] ?: 'Sem observacao adicional.',
            'actor' => $row['full_name'] ?: 'Fox Platform',
            'created_at' => $this->formatDateTime((string) $row['created_at']),
        ], $statement->fetchAll() ?: []);
    }

    private function recordApprovalReview(string $entityType, string $entityId, string $decision, string $note, string $userId): void
    {
        $statement = $this->pdo->prepare(
            sprintf(
                "INSERT INTO approval_reviews (
                    id, entity_type, entity_id, decision, note, actor_user_id
                 ) VALUES (
                    %s, :entity_type, :entity_id, :decision, :note, :actor_user_id
                 )",
                $this->uuidExpression()
            )
        );
        $statement->execute([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'decision' => $decision,
            'note' => $note,
            'actor_user_id' => $userId,
        ]);
    }

    private function findOrderRecord(string $orderId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT id, status, accepted_at, completed_at, cancelled_at
             FROM orders
             WHERE id = :order_id
             LIMIT 1"
        );
        $statement->execute([
            'order_id' => $orderId,
        ]);

        return $statement->fetch();
    }

    private function formatPartnerApprovalDetail(array $row): array
    {
        return [
            'id' => $row['id'],
            'name' => $row['trade_name'],
            'legal_name' => $row['legal_name'],
            'document_number' => $row['document_number'],
            'status' => $row['status'] === 'rejected' ? 'revisao manual' : ($row['status'] === 'active' ? 'ativo' : 'documentacao pendente'),
            'status_type' => $row['status'] === 'rejected' ? 'danger' : ($row['status'] === 'active' ? 'success' : 'warning'),
            'city' => $row['city'] ?: '-',
            'state' => $row['state'] ?: '-',
            'store_email' => $row['email'] ?: '-',
            'store_phone' => $row['phone'] ?: '-',
            'owner_name' => $row['owner_name'] ?: '-',
            'owner_email' => $row['owner_email'] ?: '-',
            'owner_phone' => $row['owner_phone'] ?: '-',
            'account_status' => $row['account_status'] === 'active' ? 'ativa' : ($row['account_status'] === 'rejected' ? 'revisao manual' : 'pendente'),
        ];
    }

    private function formatDriverApprovalDetail(array $row): array
    {
        return [
            'id' => $row['id'],
            'name' => $row['full_name'],
            'email' => $row['email'] ?: '-',
            'phone' => $row['phone'] ?: '-',
            'modal' => ucfirst((string) $row['modal']),
            'status' => $row['status'] === 'rejected' ? 'revisao manual' : ($row['status'] === 'active' ? 'ativo' : 'documentacao pendente'),
            'status_type' => $row['status'] === 'rejected' ? 'danger' : ($row['status'] === 'active' ? 'success' : 'warning'),
            'city' => $row['city'] ?: '-',
            'state' => $row['state'] ?: '-',
            'bank_account' => trim(sprintf(
                '%s %s - %s',
                $row['bank_name'] ?: 'Banco nao informado',
                $row['bank_branch_number'] ?: '-',
                $row['bank_account_number'] ?: '-'
            )),
            'rating' => $row['rating'] ? number_format((float) $row['rating'], 2, ',', '.') : '-',
            'last_active_at' => $row['last_active_at'] ? $this->formatDateTime((string) $row['last_active_at']) : '-',
        ];
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

    private function normalizeDocumentStatusLabel(string $status): string
    {
        return match ($status) {
            'approved' => 'aprovado',
            'rejected' => 'rejeitado',
            default => 'pendente',
        };
    }

    private function normalizeDocumentStatusType(string $status): string
    {
        return match ($status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    private function normalizeStoreStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'ativas',
            'paused' => 'pausadas',
            'suspended' => 'suspensas',
            'rejected' => 'rejeitadas',
            default => 'pendentes',
        };
    }

    private function normalizeDriverStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'ativos',
            'suspended' => 'suspensos',
            'rejected' => 'rejeitados',
            default => 'pendentes',
        };
    }

    private function normalizeApprovalDecisionTitle(string $decision): string
    {
        return match ($decision) {
            'approve' => 'Cadastro aprovado',
            'reject' => 'Cadastro movido para revisao',
            default => 'Observacao administrativa',
        };
    }

    private function formatDocumentMetadata(mixed $value): string
    {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return implode(' | ', array_map(
                    static fn (string $key, mixed $item): string => sprintf('%s: %s', $key, (string) $item),
                    array_keys($decoded),
                    array_values($decoded)
                ));
            }
        }

        if (is_array($value)) {
            return implode(' | ', array_map(
                static fn (string $key, mixed $item): string => sprintf('%s: %s', $key, (string) $item),
                array_keys($value),
                array_values($value)
            ));
        }

        return '-';
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
