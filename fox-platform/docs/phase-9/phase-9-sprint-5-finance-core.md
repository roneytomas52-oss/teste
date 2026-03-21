# Entrega da Fase 9 - Sprint 5 - Finance Core

## Status

Concluida em 21 de marco de 2026.

## O que foi entregue

- camada financeira minima do banco com conta bancaria da loja, repasses e movimentacoes
- endpoint real de resumo financeiro do parceiro
- endpoint real de overview financeiro do admin
- repositorios do parceiro e do admin ampliados para saldo, agenda de repasses e extrato
- telas financeiras do partner portal e do admin ajustadas para renderizacao dinamica
- SDK compartilhado expandido para consumir os novos endpoints financeiros

## Backend ligado

- [migration finance core](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/database/migrations/2026_03_20_000010_finance_core.sql)
- [seed finance core](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/database/seeders/2026_03_20_phase_9_finance_core.sql)
- [repositorio de operacao do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Infrastructure/Persistence/PdoPartnerOperationsRepository.php)
- [repositorio de operacao do admin](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Infrastructure/Persistence/PdoAdminOperationsRepository.php)
- [controller de operacao do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Interfaces/Http/Controllers/PartnerOperationsController.php)
- [controller do admin](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Interfaces/Http/Controllers/AdminController.php)

## Endpoints entregues nesta sprint

- `GET /api/v1/partner/finance/summary`
- `GET /api/v1/admin/finance/overview`

## Frontend conectado

- [SDK compartilhado](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/fox-platform-sdk.js)
- [app do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/partner-app.js)
- [app do admin](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/admin-app.js)
- [financeiro do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/partner-portal/src/finance.html)
- [financeiro do admin](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/admin/src/finance.html)

## Resultado pratico

O fox-platform agora passa a expor uma leitura real da camada financeira da operacao. Com isso:

- o parceiro ja consegue visualizar saldo, repasses, conta bancaria e extrato pela API
- o admin ja consegue visualizar volume do dia, comissoes, repasses e filas de ajuste
- o mock continua como fallback controlado para nao bloquear a demonstracao quando a API nao estiver disponivel

## Limitacao real do ambiente

Nao foi possivel executar runtime nem validar a API localmente porque `php`, `composer` e `node` continuam indisponiveis neste terminal. A implementacao ficou pronta em arquivo e alinhada ao fluxo atual do SDK.

## Proxima etapa recomendada

Sprint 6 da Fase 9:

- driver core real
- perfil do entregador pela API
- disponibilidade e ganhos pela API
- telas do driver portal consumindo banco
