# Fox Platform

Novo ecossistema proprietario da Fox Delivery.

Este projeto nasce separado do 6amMart. O 6amMart entra apenas como referencia funcional para levantamento de modulos, regras de negocio e jornadas operacionais.

## Objetivo

Construir um sistema proprio para a Fox Delivery, reutilizavel em qualquer ambiente, com:

- portal do parceiro
- portal do entregador
- painel administrativo
- landing publica
- API propria
- banco proprio
- identidade visual propria

## Principios

- sem dependencia estrutural do 6amMart
- sem reaproveitar views antigas do painel legado
- banco, autenticacao e APIs proprios
- arquitetura modular
- experiencia focada em operacao real
- API-first desde a fundacao

## Estrutura inicial

```text
fox-platform/
  apps/
    api/
    admin/
    partner-portal/
    driver-portal/
    landing/
  packages/
    core/
    sdk/
    ui/
  infra/
  docs/
```

## Documentos principais

- [Arquitetura geral](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/architecture-overview.md)
- [Decisao de stack](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/stack-decision.md)
- [Dominios de banco](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/database-domains.md)
- [Auth e RBAC](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/auth-rbac-foundation.md)
- [Bootstrap do backend](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/backend-bootstrap.md)
- [Entrega da Fase 1](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-1/phase-1-foundation.md)
- [Entrega da Fase 2](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-2/phase-2-shells.md)
- [Entrega da Fase 3](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-3/phase-3-partner-portal-mvp.md)
- [Mapa do portal do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/partner-portal-sitemap.md)
- [Superficie de APIs](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/api-surface.md)
- [Roadmap do MVP](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/roadmap-mvp.md)

## Estado atual

Fase atual: Fase 3 concluida.

Ja definido e materializado:

- fundacao tecnica do produto
- infraestrutura local via Docker Compose
- base de autenticacao e RBAC
- migrations iniciais do banco proprio
- esqueleto tecnico do backend
- design system base
- shell do admin
- shell do partner portal
- shell do driver portal
- shell da landing
- MVP navegavel do portal do parceiro

Proxima etapa:

- Financeiro e relatorios do parceiro
- extrato e repasses
- resumo financeiro
- relatorios basicos da loja

