# Fase 9 - Sprint 17

## Escopo

Consolidar os CRUDs administrativos restantes ligados a:

- acessos internos do admin
- perfis e permissoes administrativas
- notificacoes operacionais do admin

## Entregas

- backend real para:
  - listar acessos internos
  - criar membro administrativo
  - editar membro administrativo
  - atualizar status de membro administrativo
  - listar notificacoes administrativas
  - marcar notificacao administrativa como lida
- repositorio real do admin ampliado em `PdoAdminOperationsRepository`
- rotas administrativas de acesso e notificacoes consolidadas no backend
- SDK com suporte a:
  - `getAdminAccess`
  - `createAdminAccessMember`
  - `updateAdminAccessMember`
  - `updateAdminAccessMemberStatus`
  - `getAdminNotifications`
  - `markAdminNotificationRead`
- tela `permissions.html` no admin
- tela `notifications.html` no admin
- navegacao do admin atualizada com os novos modulos
- seed complementar de acessos internos e notificacoes do admin
- smoke test expandido para os fluxos da sprint

## Arquivos centrais

- `apps/api/src/Infrastructure/Persistence/PdoAdminOperationsRepository.php`
- `apps/api/src/Interfaces/Http/Controllers/AdminController.php`
- `apps/api/routes/api.php`
- `apps/api/database/seeders/2026_03_22_phase_9_admin_access_core.sql`
- `packages/sdk/src/fox-platform-sdk.js`
- `packages/sdk/src/admin-app.js`
- `apps/admin/src/permissions.html`
- `apps/admin/src/notifications.html`
- `apps/admin/src/admin.css`
- `scripts/smoke-test.php`

## Validacao executada

- `php -l` em:
  - `apps/api/src/Infrastructure/Persistence/PdoAdminOperationsRepository.php`
  - `scripts/smoke-test.php`
- `node --check` em:
  - `packages/sdk/src/admin-app.js`
  - `packages/sdk/src/fox-platform-sdk.js`
- `php apps/api/scripts/seed.php`
- smoke test completo com servidor PHP local

## Casos validados no smoke

- `admin.notifications`
- `admin.notifications.read`
- `admin.access`
- `admin.access.create`
- `admin.access.update`
- `admin.access.status`

## Resultado

A Sprint 17 fecha o bloco de governanca administrativa do MVP, deixando o admin com:

- gestao basica de equipe interna
- visibilidade de perfis e permissoes por papel
- timeline de notificacoes administrativas
- validacao real em runtime junto dos fluxos anteriores

## Proximo foco recomendado

- Sprint 18 com relatorios administrativos e visao consolidada por modulo
- endurecimento de RBAC do admin por perfil funcional nas rotas ja existentes
