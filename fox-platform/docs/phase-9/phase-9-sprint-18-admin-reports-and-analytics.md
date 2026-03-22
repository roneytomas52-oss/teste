# Fase 9 - Sprint 18

## Escopo

Consolidar a leitura analitica e os relatorios administrativos do painel interno, saindo do fallback estatico antigo para um contrato real consumido pela API do admin.

## Entregas

- backend real para:
  - `GET /api/v1/admin/analytics`
  - `GET /api/v1/admin/reports`
- repositorio do admin ampliado com consolidacao de:
  - distribuicao de pedidos por status
  - distribuicao de lojas por cidade
  - destaques gerenciais
  - resumo consolidado de parceiros, entregadores, suporte e top lojas
- casos de uso:
  - `GetAdminAnalytics`
  - `GetAdminReports`
- tela `analytics.html` ajustada para renderizacao dinamica do contrato real
- nova tela `reports.html` no admin
- SDK com:
  - `getAdminAnalytics`
  - `getAdminReports`
- `admin-app.js` ligado aos novos endpoints
- fallback `admin.json` alinhado ao contrato final de analytics e relatorios
- smoke test expandido para os dois novos endpoints

## Arquivos centrais

- `apps/api/src/Domain/Admin/AdminOperationsRepository.php`
- `apps/api/src/Application/Admin/GetAdminAnalytics.php`
- `apps/api/src/Application/Admin/GetAdminReports.php`
- `apps/api/src/Infrastructure/Persistence/PdoAdminOperationsRepository.php`
- `apps/api/src/Interfaces/Http/Controllers/AdminController.php`
- `apps/api/bootstrap/container.php`
- `apps/api/routes/api.php`
- `packages/sdk/src/fox-platform-sdk.js`
- `packages/sdk/src/admin-app.js`
- `apps/admin/src/analytics.html`
- `apps/admin/src/reports.html`
- `apps/api/mock/v1/admin.json`
- `scripts/smoke-test.php`

## Validacao executada

- `php -l` em:
  - `apps/api/src/Infrastructure/Persistence/PdoAdminOperationsRepository.php`
  - `apps/api/src/Interfaces/Http/Controllers/AdminController.php`
  - `scripts/smoke-test.php`
- `node --check` em:
  - `packages/sdk/src/admin-app.js`
  - `packages/sdk/src/fox-platform-sdk.js`
- smoke test completo com servidor PHP local

## Casos validados no smoke

- `admin.analytics`
- `admin.reports`

## Resultado

A Sprint 18 fecha o bloco de leitura consolidada do admin. O painel interno passa a ter analytics e relatorios administrativos alimentados pela API real, com fallback coerente e navegacao consistente no modulo.

## Proximo foco recomendado

- endurecer RBAC do admin por perfil funcional nas rotas ja existentes
- preparar exportacoes e filtros administrativos mais profundos
