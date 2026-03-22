# Fase 9 - Sprint 11

## Objetivo

Fechar a autenticacao unificada da equipe da loja, abrir as configuracoes administrativas da plataforma e evoluir o comportamento de notificacoes para uma rotina mais proxima de tempo real.

## O que entrou

### Backend

- autenticacao do parceiro agora considera:
  - dono da loja
  - gerente vinculado a `store_team_members`
  - equipe vinculada a `store_team_members`
- `UserRepository` passa a expor o contexto de acesso do parceiro
- `Authenticate` agora resolve permissao efetiva a partir de:
  - roles do usuario
  - permissoes extras do membro da equipe
- novo middleware de permissao:
  - `RequirePermission`
- novas migrations:
  - `2026_03_20_000016_partner_team_auth_bridge.sql`
  - `2026_03_20_000017_platform_settings_core.sql`
- novos seeds:
  - `2026_03_21_phase_11_platform_settings.sql`
  - extensoes nos seeds de usuarios e equipe

### Partner Portal

- login revisado para dono e equipe autorizada
- navegacao do portal agora esconde areas sem permissao
- telas passam a respeitar o escopo do membro autenticado
- quando a conta tenta abrir uma tela sem permissao, o portal mostra um estado de acesso restrito

### Admin

- nova tela:
  - `/settings`
- configuracoes cobertas:
  - branding
  - operacao
  - notificacoes
  - seguranca
- rotas novas:
  - `GET /api/v1/admin/settings`
  - `PUT /api/v1/admin/settings`

### SDK

- sessao guarda:
  - `permissions`
  - `partnerAccess`
- `getAuthenticatedUser()` devolve permissoes e contexto do parceiro tambem no fallback
- o fallback HTTP foi endurecido:
  - quando a origem atual responde algo que nao segue o contrato da Fox Platform, o SDK volta para o modo fallback em vez de travar com erro externo

### Notificacoes

- parceiro e entregador passaram a atualizar notificacoes por polling a cada 30 segundos
- o comportamento melhora a sensacao de fila viva sem exigir socket nesta etapa

## Validacao tecnica

Testes realizados com PHP local:

- `php -l` nos arquivos centrais alterados
- `GET /health` respondendo corretamente no servidor embutido PHP

Resposta validada:

```json
{
  "success": true,
  "data": {
    "service": "fox-platform-api",
    "status": "ok"
  }
}
```

## Bloqueio atual

O login real pela API ainda nao fecha em runtime porque o PHP local desta maquina esta sem `pdo_pgsql`.

Erro observado ao testar `POST /api/v1/auth/login`:

- `DATABASE_CONNECTION_FAILED`
- `could not find driver`

Ou seja:

- a API sobe
- o roteamento base esta funcionando
- o `health` funciona
- mas o banco PostgreSQL ainda nao esta acessivel neste ambiente

## Proximo passo

Sprint seguinte focada em endurecimento operacional:

- habilitar ambiente de banco compativel com a API real
- validar login real
- validar `auth/me`
- validar rotas protegidas sem fallback
