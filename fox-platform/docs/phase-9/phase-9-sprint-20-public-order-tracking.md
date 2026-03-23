# Fase 9 - Sprint 20

## Escopo

Fechamento do fluxo publico do cliente com rastreio de pedido apos criacao pela landing.

## Entregue

- endpoint publico `GET /api/v1/public/orders/{order_number}`
- caso de uso de rastreio publico do pedido
- repositorio publico com resumo, itens e timeline do pedido
- nova tela `track.html` na landing
- integracao do SDK com `getPublicOrderTracking`
- fluxo da loja publica atualizado para gerar link de acompanhamento apos criar o pedido
- fallback local de rastreio em `landing.json`
- smoke test expandido para validar `public.order.tracking`

## Arquivos principais

- [PublicLandingRepository.php](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Domain/Public/PublicLandingRepository.php)
- [GetPublicOrderTracking.php](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Application/Public/GetPublicOrderTracking.php)
- [PublicLandingController.php](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Interfaces/Http/Controllers/PublicLandingController.php)
- [PdoPublicLandingRepository.php](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Infrastructure/Persistence/PdoPublicLandingRepository.php)
- [api.php](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/routes/api.php)
- [fox-platform-sdk.js](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/fox-platform-sdk.js)
- [landing-app.js](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/landing-app.js)
- [track.html](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/landing/src/track.html)
- [landing.json](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/mock/v1/landing.json)
- [smoke-test.php](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/scripts/smoke-test.php)

## Validacao executada

- `php -l` em:
  - `PdoPublicLandingRepository.php`
  - `PublicLandingController.php`
  - `smoke-test.php`
- `node --check` em:
  - `fox-platform-sdk.js`
  - `landing-app.js`
- smoke test completo validado contra:
  - `public.stores`
  - `public.store-detail`
  - `public.order.create`
  - `public.order.tracking`
- verificacao HTTP local das telas:
  - `index.html`
  - `stores.html`
  - `store.html`
  - `track.html`

## Resultado

O MVP local da Fox Platform ficou fechado com jornada publica minima completa:

- descobrir loja
- abrir catalogo da loja
- criar pedido guest
- acompanhar pedido pelo numero publico
