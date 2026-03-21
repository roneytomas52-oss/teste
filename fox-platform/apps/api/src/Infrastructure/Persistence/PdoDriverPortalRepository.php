<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Driver\DriverPortalRepository;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class PdoDriverPortalRepository implements DriverPortalRepository
{
    public function __construct(
        private readonly PDO $pdo
    ) {
    }

    public function getDashboard(string $userId): array
    {
        $driver = $this->resolveDriver($userId);
        $weekly = $this->loadWeeklyMetrics($driver['id']);
        $availability = $this->loadAvailabilityRows($driver['id']);
        $recentRuns = $this->loadRecentRuns($driver['id']);
        $documents = $this->loadDocumentMetrics($driver['id']);

        return [
            'driver' => [
                'id' => $driver['id'],
                'name' => $driver['full_name'],
                'modal' => ucfirst((string) $driver['modal']),
                'status' => $driver['status'],
            ],
            'heroTitle' => 'Ganhos, documentacao e disponibilidade organizados em uma experiencia propria.',
            'heroLead' => 'O portal do entregador concentra a parte financeira, os documentos e os proximos passos da operacao.',
            'summary' => [
                ['label' => 'ganhos liquidos acumulados', 'value' => $this->formatMoney($weekly['net_earnings'])],
                ['label' => 'entregas concluidas', 'value' => (string) $weekly['completed_runs']],
                ['label' => 'presenca nas janelas abertas', 'value' => sprintf('%d%%', $this->calculatePresence($availability))],
            ],
            'metrics' => [
                ['label' => 'modalidade ativa na operacao', 'value' => ucfirst((string) $driver['modal'])],
                ['label' => 'tempo medio por corrida', 'value' => sprintf('%d min', max(1, (int) round((float) $weekly['avg_run_minutes'])))],
                ['label' => 'ganho medio por entrega', 'value' => $this->formatMoney($weekly['average_per_run'])],
                ['label' => 'avaliacao media recente', 'value' => number_format((float) $driver['rating'], 1, ',', '.')],
            ],
            'recent_runs' => $recentRuns,
            'checklist' => [
                sprintf('%d documentos aprovados na conta operacional.', $documents['approved_documents']),
                $driver['bank_account_number'] ? 'Conta bancaria configurada para repasses.' : 'Conta bancaria ainda precisa de validacao.',
                sprintf('Ultima atividade registrada em %s.', $this->formatDateTime($driver['last_active_at'])),
            ],
        ];
    }

    public function getProfile(string $userId): array
    {
        $driver = $this->resolveDriver($userId);
        $documents = $this->loadDocumentMetrics($driver['id']);

        return [
            'full_name' => $driver['full_name'],
            'email' => $driver['email'],
            'phone' => $driver['phone'],
            'modal' => ucfirst((string) $driver['modal']),
            'city' => $driver['city'],
            'bank_name' => $driver['bank_name'] ?: 'Banco Fox',
            'bank_branch_number' => $driver['bank_branch_number'] ?: '-',
            'bank_account_number' => $driver['bank_account_number'] ?: '-',
            'bank_account' => sprintf('%s %s - %s', $driver['bank_name'] ?: 'Banco Fox', $driver['bank_branch_number'] ?: '-', $driver['bank_account_number'] ?: '-'),
            'status' => $this->normalizeDriverStatusLabel($driver['status']),
            'status_type' => $this->normalizeDriverStatusType($driver['status']),
            'last_login_at' => $this->formatDateTime($driver['last_active_at']),
            'documents_status' => $documents['pending_documents'] > 0 ? 'pendentes' : 'validados',
            'documents_status_type' => $documents['pending_documents'] > 0 ? 'warning' : 'success',
        ];
    }

    public function updateProfile(string $userId, array $data): array
    {
        $driver = $this->resolveDriver($userId);

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
                'user_id' => $userId,
            ]);

            $driverUpdate = $this->pdo->prepare(
                "UPDATE driver_profiles
                 SET modal = :modal,
                     city = :city,
                     bank_name = :bank_name,
                     bank_branch_number = :bank_branch_number,
                     bank_account_number = :bank_account_number,
                     updated_at = NOW()
                 WHERE id = :driver_profile_id"
            );
            $driverUpdate->execute([
                'modal' => strtolower($data['modal']),
                'city' => $data['city'],
                'bank_name' => $data['bank_name'],
                'bank_branch_number' => $data['bank_branch_number'],
                'bank_account_number' => $data['bank_account_number'],
                'driver_profile_id' => $driver['id'],
            ]);

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }

        return $this->getProfile($userId);
    }

    public function getEarnings(string $userId): array
    {
        $driver = $this->resolveDriver($userId);
        $metrics = $this->loadWeeklyMetrics($driver['id']);
        $transactions = $this->loadEarningTransactions($driver['id']);
        $nextPayoutDate = $this->loadNextScheduledPayoutDate($driver['id']);

        return [
            'balance' => $this->formatMoney($metrics['net_earnings']),
            'balanceNote' => 'Valor acumulado no periodo atual, ja considerando ganhos por corrida e ajustes da operacao.',
            'stats' => [
                ['label' => 'ganho medio por entrega', 'value' => $this->formatMoney($metrics['average_per_run'])],
                ['label' => 'corridas concluidas na semana', 'value' => (string) $metrics['completed_runs']],
                ['label' => 'repasse previsto', 'value' => $nextPayoutDate],
            ],
            'transactions' => $transactions,
        ];
    }

    public function getAvailability(string $userId): array
    {
        $driver = $this->resolveDriver($userId);
        $slots = $this->loadAvailabilityRows($driver['id']);

        return [
            'metrics' => [
                ['label' => 'presenca nas janelas abertas', 'value' => sprintf('%d%%', $this->calculatePresence($slots))],
                ['label' => 'janelas ativas nesta semana', 'value' => (string) count(array_filter($slots, static fn (array $slot) => $slot['status_key'] !== 'closed'))],
                ['label' => 'area principal da operacao', 'value' => $driver['city'] ?: 'Nao definida'],
                ['label' => 'maior volume de corrida', 'value' => '12h-14h'],
            ],
            'slots' => $slots,
        ];
    }

    public function getDocuments(string $userId): array
    {
        $driver = $this->resolveDriver($userId);
        $documents = $this->loadDocumentRows($driver['id']);
        $metrics = $this->loadDocumentMetrics($driver['id']);

        return [
            'summary' => [
                [
                    'label' => 'documentos aprovados',
                    'value' => (string) $metrics['approved_documents'],
                ],
                [
                    'label' => 'documentos pendentes',
                    'value' => (string) $metrics['pending_documents'],
                ],
                [
                    'label' => 'modalidade ativa',
                    'value' => ucfirst((string) $driver['modal']),
                ],
            ],
            'documents' => $documents,
            'checklist' => [
                sprintf('%d documento(s) aprovado(s) para operacao atual.', $metrics['approved_documents']),
                $metrics['pending_documents'] > 0
                    ? 'Existem pendencias que precisam de revisao antes do proximo ciclo.'
                    : 'Nao ha pendencias documentais bloqueando a operacao.',
                $driver['bank_account_number']
                    ? 'Conta de recebimento registrada para repasses.'
                    : 'Conta de recebimento ainda precisa ser informada.',
            ],
            'pending_actions' => array_values(array_filter([
                $metrics['pending_documents'] > 0
                    ? [
                        'title' => 'Revisar arquivos pendentes',
                        'text' => 'Atualize os documentos sinalizados antes da proxima analise da equipe operacional.',
                    ]
                    : null,
                !$driver['bank_account_number']
                    ? [
                        'title' => 'Cadastrar conta de recebimento',
                        'text' => 'Informe a conta bancaria para liberar repasses automatizados.',
                    ]
                    : null,
            ])),
        ];
    }

    public function getSupport(string $userId): array
    {
        $driver = $this->resolveDriver($userId);

        return [
            'tickets' => $this->loadSupportTicketsByDriver($driver['id']),
        ];
    }

    public function createSupportTicket(string $userId, array $data): array
    {
        $driver = $this->resolveDriver($userId);

        $this->pdo->beginTransaction();

        try {
            $ticketInsert = $this->pdo->prepare(
                "INSERT INTO support_tickets (
                    id, scope, driver_profile_id, created_by_user_id, channel, assigned_team, priority, status, subject, description, last_message_at
                 ) VALUES (
                    gen_random_uuid(), 'driver', :driver_profile_id, :created_by_user_id, :channel, :assigned_team, :priority, 'open', :subject, :description, NOW()
                 )
                 RETURNING id"
            );
            $ticketInsert->execute([
                'driver_profile_id' => $driver['id'],
                'created_by_user_id' => $userId,
                'channel' => strtolower($data['channel']),
                'assigned_team' => $this->resolveSupportTeam($data['channel']),
                'priority' => $data['priority'],
                'subject' => $data['subject'],
                'description' => $data['description'],
            ]);
            $ticketId = (string) $ticketInsert->fetchColumn();

            $messageInsert = $this->pdo->prepare(
                "INSERT INTO support_messages (
                    id, ticket_id, sender_user_id, sender_role, body
                 ) VALUES (
                    gen_random_uuid(), :ticket_id, :sender_user_id, 'driver', :body
                 )"
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
        $driver = $this->resolveDriver($userId);
        $ticket = $this->findSupportTicketByDriver($driver['id'], $ticketId);

        if (!$ticket) {
            throw new ApiException(404, 'SUPPORT_TICKET_NOT_FOUND', 'Chamado nao encontrado para este entregador.');
        }

        return [
            'ticket' => $this->formatSupportTicket($ticket),
            'messages' => $this->loadSupportThreadMessages((string) $ticket['id'], 'driver'),
        ];
    }

    public function replySupportThread(string $userId, string $ticketId, array $data): array
    {
        $driver = $this->resolveDriver($userId);
        $ticket = $this->findSupportTicketByDriver($driver['id'], $ticketId);

        if (!$ticket) {
            throw new ApiException(404, 'SUPPORT_TICKET_NOT_FOUND', 'Chamado nao encontrado para este entregador.');
        }

        $this->pdo->beginTransaction();

        try {
            $messageInsert = $this->pdo->prepare(
                "INSERT INTO support_messages (
                    id, ticket_id, sender_user_id, sender_role, body
                 ) VALUES (
                    gen_random_uuid(), :ticket_id, :sender_user_id, 'driver', :body
                 )"
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
             WHERE scope = 'driver' AND user_id = :user_id
             ORDER BY is_read ASC, created_at DESC
             LIMIT 30"
        );
        $statement->execute(['user_id' => $userId]);
        $rows = $statement->fetchAll() ?: [];

        return [
            'summary' => [
                ['label' => 'nao lidas', 'value' => (string) count(array_filter($rows, static fn (array $row) => !(bool) $row['is_read']))],
                ['label' => 'prioridade alta', 'value' => (string) count(array_filter($rows, static fn (array $row) => in_array($row['level'], ['warning', 'danger'], true)))],
                ['label' => 'total recente', 'value' => (string) count($rows)],
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
               AND scope = 'driver'
               AND user_id = :user_id"
        );
        $statement->execute([
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);

        if ($statement->rowCount() === 0) {
            throw new ApiException(404, 'NOTIFICATION_NOT_FOUND', 'Notificacao nao encontrada para este entregador.');
        }

        return $this->getNotifications($userId);
    }

    private function resolveDriver(string $userId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                dp.id,
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
             WHERE dp.user_id = :user_id
             LIMIT 1"
        );
        $statement->execute(['user_id' => $userId]);
        $driver = $statement->fetch();

        if (!$driver) {
            throw new ApiException(404, 'DRIVER_NOT_FOUND', 'Nao foi possivel localizar o perfil do entregador.');
        }

        return $driver;
    }

    private function loadWeeklyMetrics(string $driverProfileId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                COALESCE(
                    (
                        SELECT SUM(CASE WHEN direction = 'credit' THEN amount ELSE -amount END)
                        FROM driver_wallet_transactions
                        WHERE driver_profile_id = :driver_profile_id
                    ),
                    0
                ) AS net_earnings,
                COALESCE(
                    (
                        SELECT COUNT(*)
                        FROM orders
                        WHERE driver_profile_id = :driver_profile_id
                          AND status = 'completed'
                    ),
                    0
                ) AS completed_runs,
                COALESCE(
                    (
                        SELECT AVG(EXTRACT(EPOCH FROM (completed_at - placed_at)) / 60)
                        FROM orders
                        WHERE driver_profile_id = :driver_profile_id
                          AND status = 'completed'
                          AND completed_at IS NOT NULL
                    ),
                    0
                ) AS avg_run_minutes,
                COALESCE(
                    (
                        SELECT
                            SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END)
                            / NULLIF(
                                (
                                    SELECT COUNT(*)
                                    FROM orders
                                    WHERE driver_profile_id = :driver_profile_id
                                      AND status = 'completed'
                                ),
                                0
                            )
                        FROM driver_wallet_transactions
                        WHERE driver_profile_id = :driver_profile_id
                    ),
                    0
                ) AS average_per_run"
        );
        $statement->execute(['driver_profile_id' => $driverProfileId]);

        return $statement->fetch() ?: [];
    }

    private function loadRecentRuns(string $driverProfileId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT order_number, status, total, placed_at
             FROM orders
             WHERE driver_profile_id = :driver_profile_id
             ORDER BY placed_at DESC
             LIMIT 4"
        );
        $statement->execute(['driver_profile_id' => $driverProfileId]);

        return array_map(fn (array $row) => [
            'id' => '#RUN-' . substr($row['order_number'], -4),
            'status' => $this->normalizeRunStatusLabel($row['status']),
            'status_type' => $this->normalizeRunStatusType($row['status']),
            'value' => $this->formatMoney($row['total']),
            'time' => date('H:i', strtotime($row['placed_at'])),
        ], $statement->fetchAll() ?: []);
    }

    private function loadDocumentMetrics(string $driverProfileId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT
                COUNT(*) FILTER (WHERE status = 'approved') AS approved_documents,
                COUNT(*) FILTER (WHERE status <> 'approved') AS pending_documents
             FROM driver_documents
             WHERE driver_profile_id = :driver_profile_id"
        );
        $statement->execute(['driver_profile_id' => $driverProfileId]);

        return $statement->fetch() ?: [];
    }

    private function loadDocumentRows(string $driverProfileId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT document_type, status, note, expires_at, reviewed_at
             FROM driver_documents
             WHERE driver_profile_id = :driver_profile_id
             ORDER BY reviewed_at DESC NULLS LAST, expires_at ASC NULLS LAST, created_at DESC"
        );
        $statement->execute(['driver_profile_id' => $driverProfileId]);

        return array_map(fn (array $row) => [
            'title' => $this->normalizeDocumentTitle((string) $row['document_type']),
            'status' => $this->normalizeDocumentStatusLabel((string) $row['status']),
            'status_type' => $this->normalizeDocumentStatusType((string) $row['status']),
            'description' => $row['note'] ?: 'Documento registrado no perfil operacional.',
            'expires_at' => $row['expires_at'] ? $this->formatDate((string) $row['expires_at']) : '-',
            'reviewed_at' => $row['reviewed_at'] ? $this->formatDateTime((string) $row['reviewed_at']) : '-',
        ], $statement->fetchAll() ?: []);
    }

    private function loadEarningTransactions(string $driverProfileId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT occurred_at, description, status, amount, direction
             FROM driver_wallet_transactions
             WHERE driver_profile_id = :driver_profile_id
             ORDER BY occurred_at DESC
             LIMIT 12"
        );
        $statement->execute(['driver_profile_id' => $driverProfileId]);

        return array_map(fn (array $row) => [
            'date' => $this->formatDate($row['occurred_at']),
            'run' => $row['description'],
            'status' => $this->normalizeWalletStatusLabel($row['status']),
            'status_type' => $this->normalizeWalletStatusType($row['status']),
            'value' => $this->formatMoney($row['amount']),
            'note' => $row['direction'] === 'credit' ? 'credito operacional' : 'ajuste financeiro',
        ], $statement->fetchAll() ?: []);
    }

    private function loadNextScheduledPayoutDate(string $driverProfileId): string
    {
        $statement = $this->pdo->prepare(
            "SELECT occurred_at
             FROM driver_wallet_transactions
             WHERE driver_profile_id = :driver_profile_id AND status = 'scheduled'
             ORDER BY occurred_at ASC
             LIMIT 1"
        );
        $statement->execute(['driver_profile_id' => $driverProfileId]);
        $next = $statement->fetchColumn();

        return $next ? $this->formatDate($next) : '-';
    }

    private function loadAvailabilityRows(string $driverProfileId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT weekday, starts_at, ends_at, status
             FROM driver_availability_slots
             WHERE driver_profile_id = :driver_profile_id
             ORDER BY weekday ASC, starts_at ASC"
        );
        $statement->execute(['driver_profile_id' => $driverProfileId]);

        $grouped = [];
        foreach ($statement->fetchAll() ?: [] as $row) {
            $weekday = (int) $row['weekday'];
            if (!isset($grouped[$weekday])) {
                $grouped[$weekday] = [
                    'weekday' => $weekday,
                    'title' => $this->weekdayLabel($weekday),
                    'status_key' => $row['status'],
                    'status' => $this->normalizeAvailabilityStatusLabel($row['status']),
                    'status_type' => $this->normalizeAvailabilityStatusType($row['status']),
                    'windows' => [],
                ];
            }

            $grouped[$weekday]['windows'][] = sprintf('%s as %s', substr((string) $row['starts_at'], 0, 5), substr((string) $row['ends_at'], 0, 5));
            if ($row['status'] === 'open') {
                $grouped[$weekday]['status_key'] = 'open';
                $grouped[$weekday]['status'] = $this->normalizeAvailabilityStatusLabel('open');
                $grouped[$weekday]['status_type'] = $this->normalizeAvailabilityStatusType('open');
            }
        }

        return array_map(
            fn (array $row) => [
                ...$row,
                'description' => $row['windows'] ? implode(' e ', $row['windows']) : 'sem janelas abertas no momento',
            ],
            array_values($grouped)
        );
    }

    private function loadSupportTicketsByDriver(string $driverProfileId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, channel, priority, status, subject, assigned_team, created_at, last_message_at
             FROM support_tickets
             WHERE driver_profile_id = :driver_profile_id
             ORDER BY last_message_at DESC, created_at DESC
             LIMIT 20"
        );
        $statement->execute(['driver_profile_id' => $driverProfileId]);

        return array_map(fn (array $row) => $this->formatSupportTicket($row), $statement->fetchAll() ?: []);
    }

    private function findSupportTicketByDriver(string $driverProfileId, string $ticketId): array|false
    {
        $statement = $this->pdo->prepare(
            "SELECT id, channel, priority, status, subject, assigned_team, created_at, last_message_at
             FROM support_tickets
             WHERE driver_profile_id = :driver_profile_id
               AND id = :ticket_id
             LIMIT 1"
        );
        $statement->execute([
            'driver_profile_id' => $driverProfileId,
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
                'author' => $direction === 'outgoing' ? 'Entregador' : ($row['full_name'] ?: 'Fox Delivery'),
                'body' => $row['body'],
                'time' => $this->formatDateTime((string) $row['created_at']),
                'role' => $row['sender_role'],
            ];
        }, $statement->fetchAll() ?: []);
    }

    private function calculatePresence(array $slots): int
    {
        if (!$slots) {
            return 0;
        }

        $open = count(array_filter($slots, static fn (array $slot) => $slot['status_key'] === 'open'));
        return (int) round(($open / count($slots)) * 100);
    }

    private function normalizeDriverStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'ativa',
            'pending' => 'pendente',
            'rejected' => 'rejeitada',
            default => 'suspensa',
        };
    }

    private function normalizeDriverStatusType(string $status): string
    {
        return match ($status) {
            'active' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    private function normalizeRunStatusLabel(string $status): string
    {
        return match ($status) {
            'completed' => 'concluida',
            'cancelled' => 'cancelada',
            default => 'em analise',
        };
    }

    private function normalizeRunStatusType(string $status): string
    {
        return match ($status) {
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'warning',
        };
    }

    private function normalizeWalletStatusLabel(string $status): string
    {
        return match ($status) {
            'processed' => 'concluida',
            'scheduled' => 'agendado',
            default => 'ajuste em analise',
        };
    }

    private function normalizeWalletStatusType(string $status): string
    {
        return match ($status) {
            'processed' => 'success',
            'scheduled' => 'warning',
            default => 'warning',
        };
    }

    private function normalizeAvailabilityStatusLabel(string $status): string
    {
        return match ($status) {
            'open' => 'aberta',
            'partial' => 'parcial',
            default => 'fechada',
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

    private function normalizeSupportChannelLabel(string $channel): string
    {
        return match ($channel) {
            'earnings' => 'Ganhos',
            'documents' => 'Documentos',
            'operations' => 'Operacao',
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

    private function formatSupportTicket(array $row): array
    {
        return [
            'id' => '#DRV-' . strtoupper(substr(str_replace('-', '', (string) $row['id']), 0, 4)),
            'ticket_id' => $row['id'],
            'summary' => $row['subject'],
            'channel' => $this->normalizeSupportChannelLabel((string) $row['channel']),
            'status' => $this->normalizeSupportStatusLabel((string) $row['status']),
            'statusType' => $this->normalizeSupportStatusType((string) $row['status']),
            'priority' => $this->normalizeSupportPriorityLabel((string) $row['priority']),
            'assigned_team' => $row['assigned_team'] ?: 'operacao',
            'meta' => [
                $this->normalizeSupportChannelLabel((string) $row['channel']),
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
            'success' => 'confirmado',
            'warning' => 'requer atencao',
            'danger' => 'urgente',
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
            'earnings' => 'Ganhos',
            'documents' => 'Documentos',
            'availability' => 'Disponibilidade',
            default => 'Operacao',
        };
    }

    private function normalizeDocumentTitle(string $type): string
    {
        return match ($type) {
            'identity' => 'Documento de identidade',
            'cnh' => 'CNH',
            'vehicle' => 'Documento do veiculo',
            default => 'Documento complementar',
        };
    }

    private function normalizeDocumentStatusLabel(string $status): string
    {
        return match ($status) {
            'approved' => 'validado',
            'pending' => 'pendente',
            'rejected' => 'reprovado',
            default => 'em revisao',
        };
    }

    private function normalizeDocumentStatusType(string $status): string
    {
        return match ($status) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

    private function resolveSupportTeam(string $channel): string
    {
        return match (strtolower($channel)) {
            'earnings' => 'financeiro',
            'documents' => 'cadastro',
            default => 'operacao',
        };
    }

    private function normalizeAvailabilityStatusType(string $status): string
    {
        return match ($status) {
            'open' => 'success',
            'partial' => 'warning',
            default => 'danger',
        };
    }

    private function weekdayLabel(int $weekday): string
    {
        return match ($weekday) {
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terca-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            default => 'Sabado',
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

        return date('d/m/Y \a\s H:i', strtotime($value));
    }

    private function formatMoney(float|int|string|null $value): string
    {
        return 'R$ ' . number_format((float) ($value ?? 0), 2, ',', '.');
    }
}
