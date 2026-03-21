<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Admin\AdminOperationsRepository;

class PdoAdminOperationsRepository implements AdminOperationsRepository
{
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
            "SELECT o.order_number, o.customer_name, o.status, o.total, o.placed_at,
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
            "SELECT
                COALESCE((SELECT SUM(total) FROM orders WHERE DATE(placed_at) = CURRENT_DATE AND status <> 'cancelled'), 0) AS gross_today,
                COALESCE((SELECT SUM(amount) FROM wallet_transactions WHERE transaction_type = 'platform_fee' AND DATE(occurred_at) = CURRENT_DATE), 0) AS fees_today,
                COALESCE((SELECT SUM(amount) FROM payout_requests WHERE scheduled_for::date BETWEEN CURRENT_DATE AND CURRENT_DATE + INTERVAL '7 days' AND status IN ('scheduled', 'processing')), 0) AS payouts_week,
                COALESCE((SELECT COUNT(*) FROM wallet_transactions WHERE transaction_type IN ('adjustment', 'refund') AND status = 'under_review'), 0) AS pending_adjustments"
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
                COUNT(*) FILTER (WHERE status IN ('open', 'in_progress')) AS active_queue,
                COUNT(*) FILTER (WHERE priority = 'critical') AS critical_queue,
                COUNT(*) FILTER (WHERE status = 'resolved') AS resolved_queue
             FROM support_tickets"
        );
        $row = $statement->fetch() ?: [];

        return [
            sprintf('%d protocolos na fila ativa.', (int) ($row['active_queue'] ?? 0)),
            sprintf('%d chamados em prioridade critica.', (int) ($row['critical_queue'] ?? 0)),
            sprintf('%d chamados ja concluídos neste ciclo.', (int) ($row['resolved_queue'] ?? 0)),
        ];
    }

    private function formatOrderRow(array $row): array
    {
        [$statusLabel, $statusType] = match ($row['status']) {
            'pending_acceptance' => ['aguardando aceite', 'warning'],
            'accepted' => ['aceito', 'success'],
            'preparing' => ['em preparo', 'success'],
            'ready_for_pickup' => ['pronto para retirada', 'warning'],
            'on_route' => ['em rota', 'success'],
            'completed' => ['concluido', 'success'],
            'cancelled' => ['cancelado', 'danger'],
            default => [$row['status'], 'warning'],
        };

        return [
            'id' => '#' . $row['order_number'],
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

    private function formatDate(?string $value): string
    {
        if (!$value) {
            return '-';
        }

        return date('d/m/Y', strtotime($value));
    }

    private function formatMoney(float|int|string|null $value): string
    {
        return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
