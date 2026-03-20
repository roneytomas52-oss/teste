# Fox Platform

Novo ecossistema proprietário da Fox Delivery.

Este projeto nasce separado do 6amMart. O 6amMart entra apenas como referência funcional para levantamento de módulos, regras de negócio e jornadas operacionais.

## Objetivo

Construir um sistema próprio para a Fox Delivery, reutilizável em qualquer ambiente, com:

- portal do parceiro
- portal do entregador
- painel administrativo
- landing pública
- API própria
- banco próprio
- identidade visual própria

## Princípios

- sem dependência estrutural do 6amMart
- sem reaproveitar views antigas do painel legado
- banco, autenticação e APIs próprios
- arquitetura modular
- experiência focada em operação real
- API-first desde a fundação

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
- [Decisão de stack](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/stack-decision.md)
- [Domínios de banco](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/database-domains.md)
- [Auth e RBAC](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/auth-rbac-foundation.md)
- [Bootstrap do backend](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/backend-bootstrap.md)
- [Entrega da Fase 1](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-1/phase-1-foundation.md)
- [Entrega da Fase 2](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-2/phase-2-shells.md)
- [Mapa do portal do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/partner-portal-sitemap.md)
- [Superfície de APIs](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/api-surface.md)
- [Roadmap do MVP](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/roadmap-mvp.md)

## Estado atual

Fase atual: Fase 2 concluída.

Já definido e materializado:

- fundação técnica do produto
- infraestrutura local via Docker Compose
- base de autenticação e RBAC
- migrations iniciais do banco próprio
- esqueleto técnico do backend
- design system base
- shell do admin
- shell do partner portal
- shell do driver portal
- shell da landing

Próxima etapa:

- Partner Portal MVP
- login e autenticação real
- dashboard da loja
- pedidos, catálogo, estoque e perfil

