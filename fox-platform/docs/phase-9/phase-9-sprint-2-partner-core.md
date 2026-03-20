# Entrega da Fase 9 - Sprint 2 - Partner Core

## Status

Concluida em 20 de marco de 2026.

## O que foi entregue

- rotas reais do parceiro no backend HTTP
- container ligado ao dominio de parceiro
- repositorio PDO para perfil, loja, horarios e documentos
- migrations de `store_hours`, `store_documents` e descricao da loja
- seed inicial para horarios e documento da loja
- SDK do portal do parceiro com tentativa de API real e fallback local
- telas de perfil, loja e horarios preparadas para leitura e salvamento

## Backend ligado

- [container](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/bootstrap/container.php)
- [rotas](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/routes/api.php)
- [controller](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Interfaces/Http/Controllers/PartnerController.php)
- [repository](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Infrastructure/Persistence/PdoPartnerPortalRepository.php)

## Endpoints entregues nesta sprint

- `GET /api/v1/partner/profile`
- `PUT /api/v1/partner/profile`
- `GET /api/v1/partner/store`
- `PUT /api/v1/partner/store`
- `PUT /api/v1/partner/store/hours`
- `POST /api/v1/partner/store/documents`

## Frontend conectado

- [SDK compartilhado](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/fox-platform-sdk.js)
- [app do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/partner-app.js)
- [login](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/partner-portal/src/login.html)
- [perfil](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/partner-portal/src/profile.html)
- [loja](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/partner-portal/src/store.html)
- [horarios](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/partner-portal/src/schedules.html)

## Resultado pratico

O Partner Portal deixou de depender apenas do mock para o nucleo da conta da loja. Agora:

- login tenta API real antes do fallback
- perfil do parceiro pode ser lido e salvo
- dados da loja podem ser lidos e salvos
- horarios podem ser atualizados
- documentos podem ser registrados

Quando a API nao estiver acessivel no ambiente, o portal continua funcionando com fallback local para nao bloquear o fluxo visual do MVP.

## Limitacao real do ambiente

Nao foi possivel executar o backend nem validar runtime porque `php`, `composer` e `node` continuam indisponiveis neste terminal. A entrega foi implementada em arquivo e conectada no frontend com fallback para preservar navegacao e demonstracao.

## Proxima etapa recomendada

Sprint 3 da Fase 9:

- catalogo real
- estoque real
- pedidos reais
- dashboard do parceiro alimentado por banco
