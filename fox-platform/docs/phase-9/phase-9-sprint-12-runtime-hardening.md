# Fase 9 - Sprint 12: Runtime hardening

## Objetivo

Fechar a operacao local do `fox-platform` com:

- API servida pelo PHP usando um router valido para todas as rotas HTTP
- servidor estatico unico para os apps web
- comando unico para subir o stack local
- smoke test validado contra a API real

## Entregas

- `apps/api/public/router.php` para servir a API via `php -S`
- `scripts/serve-static.js` para publicar os apps web em HTTP local
- `scripts/dev-stack.js` para subir API e frontend juntos
- `package.json` no root do `fox-platform` com scripts de desenvolvimento e smoke
- ajuste final do contrato de `admin/settings` para MySQL
- ajuste final do modulo de documentos do entregador para o schema real do banco

## Validacao

O smoke test local passou por completo com:

- health
- login admin, partner e driver
- `auth/me`
- partner profile/store/dashboard/catalog/orders/finance/team/support/notifications
- admin dashboard/settings/support/orders/approvals
- driver dashboard/profile/earnings/documents/support/notifications
- categorias e metricas publicas
- captura publica de lead de parceiro e entregador

## Comandos

Subir o stack local:

```bash
node scripts/dev-stack.js
```

Rodar smoke test:

```bash
php scripts/smoke-test.php
```

## Estado apos a sprint

O `fox-platform` deixa de depender de abertura manual de arquivos HTML e passa a ter um fluxo local repetivel para subir, navegar e validar API + apps.
