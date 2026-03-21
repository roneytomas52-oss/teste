# Fase 9 - Sprint 8 - Public Landing Core

## Objetivo

Conectar a landing publica a uma camada real de categorias, metricas e captacao de leads.

## Entregas

- dominio publico criado no backend
- controller publico com:
  - categorias
  - metricas da plataforma
  - lead de parceiro
  - lead de entregador
- migration para:
  - `partner_leads`
  - `driver_leads`
- rotas reais:
  - `GET /api/v1/public/categories`
  - `GET /api/v1/public/platform-metrics`
  - `POST /api/v1/public/partner-leads`
  - `POST /api/v1/public/driver-leads`
- SDK publico da landing
- landing ligada aos novos endpoints

## Arquivos centrais

- `apps/api/src/Infrastructure/Persistence/PdoPublicLandingRepository.php`
- `apps/api/src/Interfaces/Http/Controllers/PublicLandingController.php`
- `apps/api/database/migrations/2026_03_20_000013_public_leads_core.sql`
- `packages/sdk/src/landing-app.js`
- `packages/sdk/src/fox-platform-sdk.js`
- `apps/landing/src/index.html`
- `apps/api/mock/v1/landing.json`

## Resultado

- categorias publicas passam a vir do catalogo/plataforma
- metricas publicas passam a vir do banco
- landing registra leads reais de parceiro e entregador

## Limites atuais

- a landing ainda e uma experiencia web simples, sem checkout cliente
- ainda nao existe funil completo de onboarding comercial publico
