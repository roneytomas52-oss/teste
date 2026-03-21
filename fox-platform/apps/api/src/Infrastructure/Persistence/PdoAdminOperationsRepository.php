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

    private function formatMoney(float|int|string|null $value): string
    {
        return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
