<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use FoxPlatform\Api\Domain\Public\PublicLandingRepository;

class PdoPublicLandingRepository implements PublicLandingRepository
{
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
                COUNT(p.id) FILTER (WHERE p.status = 'active') AS active_products
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

    public function createPartnerLead(array $data): array
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO partner_leads (
                id, company_name, contact_name, email, phone, city, business_type, status, source
             ) VALUES (
                gen_random_uuid(), :company_name, :contact_name, :email, :phone, :city, :business_type, 'new', 'landing'
             )
             RETURNING id, created_at"
        );
        $statement->execute([
            'company_name' => $data['company_name'],
            'contact_name' => $data['contact_name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'city' => $data['city'],
            'business_type' => $data['business_type'],
        ]);

        $lead = $statement->fetch() ?: [];

        return [
            'protocol' => 'PAR-' . strtoupper(substr(str_replace('-', '', (string) ($lead['id'] ?? '')), 0, 8)),
            'status' => 'recebido',
            'next_step' => 'Nossa equipe comercial vai analisar o perfil da operacao e retornar com os proximos passos.',
        ];
    }

    public function createDriverLead(array $data): array
    {
        $statement = $this->pdo->prepare(
            "INSERT INTO driver_leads (
                id, full_name, email, phone, city, modal, status, source
             ) VALUES (
                gen_random_uuid(), :full_name, :email, :phone, :city, :modal, 'new', 'landing'
             )
             RETURNING id, created_at"
        );
        $statement->execute([
            'full_name' => $data['full_name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'city' => $data['city'],
            'modal' => $data['modal'],
        ]);

        $lead = $statement->fetch() ?: [];

        return [
            'protocol' => 'DRV-' . strtoupper(substr(str_replace('-', '', (string) ($lead['id'] ?? '')), 0, 8)),
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
}
