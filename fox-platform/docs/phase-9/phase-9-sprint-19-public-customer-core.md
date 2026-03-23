# Fase 9 - Sprint 19

## Escopo

Abrir o lado do cliente final no ecossistema da Fox Platform com descoberta publica de lojas, detalhe de loja e criacao inicial de pedido guest.

## Entregas

- backend publico ampliado com:
  - `GET /api/v1/public/stores`
  - `GET /api/v1/public/stores/{store_id}`
  - `POST /api/v1/public/orders`
- repositorio publico ampliado com:
  - listagem de lojas ativas
  - detalhe de loja com catalogo publico
  - criacao de pedido guest com persistencia em `orders`, `order_items` e `order_status_logs`
- request dedicado:
  - `PublicOrderCreateRequest`
- novos casos de uso:
  - `GetPublicStores`
  - `GetPublicStoreDetail`
  - `CreatePublicOrder`
- SDK publico com:
  - `getPublicStores`
  - `getPublicStoreDetail`
  - `createPublicOrder`
- novas telas da landing:
  - `stores.html`
  - `store.html`
- CSS compartilhado da jornada publica em `landing.css`
- fallback `landing.json` ampliado para lojas e detalhe de loja
- smoke test expandido para cobrir o fluxo publico novo

## Arquivos centrais

- `apps/api/src/Domain/Public/PublicLandingRepository.php`
- `apps/api/src/Application/Public/GetPublicStores.php`
- `apps/api/src/Application/Public/GetPublicStoreDetail.php`
- `apps/api/src/Application/Public/CreatePublicOrder.php`
- `apps/api/src/Interfaces/Http/Controllers/PublicLandingController.php`
- `apps/api/src/Interfaces/Http/Requests/PublicOrderCreateRequest.php`
- `apps/api/src/Infrastructure/Persistence/PdoPublicLandingRepository.php`
- `apps/api/bootstrap/container.php`
- `apps/api/routes/api.php`
- `packages/sdk/src/fox-platform-sdk.js`
- `packages/sdk/src/landing-app.js`
- `apps/landing/src/index.html`
- `apps/landing/src/stores.html`
- `apps/landing/src/store.html`
- `apps/landing/src/landing.css`
- `apps/api/mock/v1/landing.json`
- `scripts/smoke-test.php`

## Validacao executada

- `php -l` em:
  - `apps/api/src/Infrastructure/Persistence/PdoPublicLandingRepository.php`
  - `apps/api/src/Interfaces/Http/Controllers/PublicLandingController.php`
  - `apps/api/src/Interfaces/Http/Requests/PublicOrderCreateRequest.php`
  - `scripts/smoke-test.php`
- `node --check` em:
  - `packages/sdk/src/fox-platform-sdk.js`
  - `packages/sdk/src/landing-app.js`
- smoke test completo com servidor PHP local

## Casos validados no smoke

- `public.categories`
- `public.metrics`
- `public.stores`
- `public.store-detail`
- `public.order.create`
- `public.partner-lead`
- `public.driver-lead`

## Resultado

O ecossistema deixa de ser apenas institucional no lado publico. Agora existe jornada real de cliente para descobrir lojas, entrar em uma vitrine publica e iniciar um pedido diretamente pela API.

## Proximo foco recomendado

- consolidar checkout e resumo do pedido
- criar consulta publica de status do pedido
- iniciar a trilha de cliente autenticado, se o produto exigir conta propria
