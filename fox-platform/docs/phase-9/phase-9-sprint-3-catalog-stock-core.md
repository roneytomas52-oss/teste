# Avanco da Fase 9 - Sprint 3 - Catalogo e estoque

## Status

Em andamento em 20 de marco de 2026.

## O que entrou neste avanco

- migration inicial de categorias, produtos e movimentos de estoque
- seed inicial do catalogo da loja demo
- repository PDO para leitura do catalogo e ajuste de estoque
- endpoints reais do parceiro para listar catalogo e atualizar inventario
- bridge do SDK para catalogo em modo API-first com fallback local
- telas de catalogo e estoque do partner portal conectadas aos dados reais/mockados

## Backend ligado

- [migration catalog core](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/database/migrations/2026_03_20_000008_catalog_core.sql)
- [seed catalog core](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/database/seeders/2026_03_20_phase_9_catalog_core.sql)
- [repository do catalogo](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Infrastructure/Persistence/PdoPartnerCatalogRepository.php)
- [controller do catalogo](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/api/src/Interfaces/Http/Controllers/PartnerCatalogController.php)

## Endpoints entregues neste avanco

- `GET /api/v1/partner/catalog/products`
- `PUT /api/v1/partner/catalog/products/{product_id}/inventory`

## Frontend conectado

- [SDK compartilhado](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/fox-platform-sdk.js)
- [app do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/packages/sdk/src/partner-app.js)
- [catalogo](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/partner-portal/src/catalog.html)
- [estoque](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/apps/partner-portal/src/inventory.html)

## O que ainda falta dentro da Sprint 3

- criacao e edicao real de produto
- categorias gerenciaveis
- pedidos reais do parceiro
- dashboard do parceiro lendo banco
- conciliacao do catalogo com relatorios e pedidos
