# Fase 9 - Sprint 9 - Admin Approval Actions

## Objetivo

Fazer o admin deixar de ser apenas leitura nas filas de aprovacao e passar a executar a aprovacao/rejeicao de parceiro e entregador.

## Entregas

- casos de uso para:
  - aprovar parceiro
  - rejeitar parceiro
  - aprovar entregador
  - rejeitar entregador
- rotas reais:
  - `POST /api/v1/admin/approvals/partners/{partner_id}/approve`
  - `POST /api/v1/admin/approvals/partners/{partner_id}/reject`
  - `POST /api/v1/admin/approvals/drivers/{driver_id}/approve`
  - `POST /api/v1/admin/approvals/drivers/{driver_id}/reject`
- repositorio admin com mutacao real de status
- SDK admin com acoes de aprovacao/rejeicao
- telas de aprovacao com botoes operacionais reais

## Arquivos centrais

- `apps/api/src/Domain/Admin/AdminOperationsRepository.php`
- `apps/api/src/Infrastructure/Persistence/PdoAdminOperationsRepository.php`
- `apps/api/src/Interfaces/Http/Controllers/AdminController.php`
- `apps/api/routes/api.php`
- `packages/sdk/src/fox-platform-sdk.js`
- `packages/sdk/src/admin-app.js`

## Resultado

- admin consegue aprovar parceiro
- admin consegue rejeitar parceiro
- admin consegue aprovar entregador
- admin consegue rejeitar entregador
- a fila do frontend reflete o estado retornado pela API

## Limites atuais

- a acao ainda nao recebe nota operacional ou motivo formal
- ainda nao existe trilha de auditoria dedicada para cada aprovacao
