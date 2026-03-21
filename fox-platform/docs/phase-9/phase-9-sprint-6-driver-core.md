# Fase 9 - Sprint 6

## Objetivo

Conectar o `driver-portal` ao backend real da Fox Platform, substituindo os principais blocos mockados por leitura e atualizacao vindas da API.

## Entregas desta sprint

- repositorio real do entregador consolidado em:
  - `apps/api/src/Infrastructure/Persistence/PdoDriverPortalRepository.php`
- casos de uso reais para:
  - dashboard
  - perfil
  - atualizacao de perfil
  - ganhos
  - disponibilidade
  - documentos
- controller HTTP do entregador em:
  - `apps/api/src/Interfaces/Http/Controllers/DriverController.php`
- rotas reais do entregador adicionadas em:
  - `apps/api/routes/api.php`
  - `apps/api/routes/v1-map.php`
- SDK em modo API-first para o entregador em:
  - `packages/sdk/src/fox-platform-sdk.js`
- app do driver reescrito para renderizacao dinamica em:
  - `packages/sdk/src/driver-app.js`

## Telas conectadas ao backend

- `apps/driver-portal/src/index.html`
- `apps/driver-portal/src/profile.html`
- `apps/driver-portal/src/earnings.html`
- `apps/driver-portal/src/availability.html`
- `apps/driver-portal/src/documents.html`

## Banco

### Migration

- `apps/api/database/migrations/2026_03_20_000011_driver_operations.sql`

### Seed

- `apps/api/database/seeders/2026_03_20_phase_9_driver_core.sql`

## Endpoints entregues

- `GET /api/v1/driver/dashboard`
- `GET /api/v1/driver/profile`
- `PUT /api/v1/driver/profile`
- `GET /api/v1/driver/earnings`
- `GET /api/v1/driver/availability`
- `GET /api/v1/driver/documents`

## Criterios de aceite cobertos em codigo

- entregador autenticado consegue ler o dashboard pela API
- entregador autenticado consegue ler o proprio perfil
- entregador autenticado consegue atualizar o proprio perfil
- tela de ganhos deixa de depender do mock para saldo, indicadores e historico
- tela de disponibilidade deixa de depender do mock para metricas e janelas
- tela de documentos passa a consumir dados reais do banco

## Observacoes

- a agregacao semanal do repositorio do entregador foi ajustada para subconsultas, evitando duplicacao de linhas entre `orders` e `driver_wallet_transactions`
- o suporte do entregador continua em fallback local ate a proxima sprint
- esta maquina continua sem `php`, `node`, `composer` e `npm/pnpm`, entao a entrega foi validada apenas no nivel de codigo

## Proximo foco recomendado

- Sprint 7 da Fase 9:
  - suporte real para parceiro, admin e entregador
  - tickets e mensagens persistidos
  - telas de suporte consumindo a API
