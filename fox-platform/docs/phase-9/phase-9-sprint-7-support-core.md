# Fase 9 - Sprint 7 - Support Core

## Objetivo

Tirar o suporte do modo mock e ligar parceiro, admin e entregador a tickets persistidos no backend.

## Entregas

- migration de suporte com:
  - `support_tickets`
  - `support_messages`
- seed inicial de chamados e mensagens
- rotas reais:
  - `GET /api/v1/partner/support`
  - `POST /api/v1/partner/support/tickets`
  - `GET /api/v1/admin/support/queue`
  - `GET /api/v1/driver/support`
  - `POST /api/v1/driver/support/tickets`
- repositórios PDO reais para suporte de parceiro, admin e entregador
- SDK em modo API-first para suporte
- telas dos portais ligadas a suporte real

## Arquivos centrais

- `apps/api/database/migrations/2026_03_20_000012_support_core.sql`
- `apps/api/database/seeders/2026_03_20_phase_9_support_core.sql`
- `apps/api/src/Infrastructure/Persistence/PdoPartnerOperationsRepository.php`
- `apps/api/src/Infrastructure/Persistence/PdoAdminOperationsRepository.php`
- `apps/api/src/Infrastructure/Persistence/PdoDriverPortalRepository.php`
- `packages/sdk/src/fox-platform-sdk.js`
- `packages/sdk/src/partner-app.js`
- `packages/sdk/src/admin-app.js`
- `packages/sdk/src/driver-app.js`

## Resultado

- parceiro abre chamado real e enxerga a fila de tickets
- admin enxerga fila prioritaria, distribuicao e SLA
- entregador abre chamado real e acompanha tickets recentes

## Limites atuais

- ainda nao existe thread completa de mensagens exposta no frontend
- ainda nao existe SLA automatizado com motor de regras
- sem validacao de runtime local por ausencia de `php` e `node`
