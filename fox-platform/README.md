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
- [Entrega da Fase 4](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-4/phase-4-finance-and-reports.md)
- [Entrega da Fase 5](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-5/phase-5-admin-mvp.md)
- [Entrega da Fase 6](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-6/phase-6-driver-portal-mvp.md)
- [Entrega da Fase 7](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-7/phase-7-support-and-refinement.md)
- [Entrega da Fase 8](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-8/phase-8-real-integration.md)
- [Entrega da Fase 9 - Sprint 1](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-1-backend-bootstrap.md)
- [Entrega da Fase 9 - Sprint 2](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-2-partner-core.md)
- [Entrega da Fase 9 - Sprint 3](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-3-catalog-stock-core.md)
- [Entrega da Fase 9 - Sprint 4](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-4-orders-and-admin-core.md)
- [Entrega da Fase 9 - Sprint 5](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-5-finance-core.md)
- [Entrega da Fase 9 - Sprint 6](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-6-driver-core.md)
- [Entrega da Fase 9 - Sprint 7](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-7-support-core.md)
- [Entrega da Fase 9 - Sprint 8](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-8-public-landing-core.md)
- [Entrega da Fase 9 - Sprint 9](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-9-admin-approval-actions.md)
- [Entrega da Fase 9 - Sprint 10](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/phase-9/phase-9-sprint-10-team-notifications-and-ticket-threads.md)
- [Mapa do portal do parceiro](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/partner-portal-sitemap.md)
- [Superficie de APIs](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/api-surface.md)
- [Roadmap do MVP](C:/Users/roney/Documents/GitHub/Usuario/teste/fox-platform/docs/roadmap-mvp.md)

## Estado atual

Fase atual: Fase 9 em andamento.

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
- financeiro e relatorios basicos do parceiro
- painel admin MVP com aprovacao, pedidos, operacao e financeiro
- portal do entregador com login, ganhos, documentos, disponibilidade e perfil
- suporte do parceiro com mensagens, chamados e ajuda
- suporte do entregador
- admin com suporte, auditoria e analytics
- camada mock versionada da API
- SDK compartilhado para os apps
- autenticacao local no navegador
- sessao integrada entre telas principais
- dashboards e modulos principais hidratados por dados compartilhados
- bootstrap HTTP do novo backend proprio
- auth real com login, logout, refresh, reset e `me`
- PDO e configuracao de banco prontos para conexao real
- migrations complementares para reset de senha e indices
- seed inicial de admin, parceiro e entregador
- rotas reais do Partner Core
- perfil, loja, horarios e documentos conectados ao backend do parceiro
- SDK do parceiro em modo API-first com fallback local
- credenciais de demonstracao alinhadas entre mock e seed inicial
- catalogo inicial e estoque da loja conectados ao backend do parceiro
- rotas reais para listagem de produtos e ajuste de inventario
- dashboard do parceiro lendo dados reais do backend
- pedidos do parceiro lendo dados reais do backend
- alteracao de status de pedidos do parceiro via API
- dashboard do admin lendo dados reais do backend
- pedidos do admin lendo dados reais do backend
- filas de aprovacao do admin lendo dados reais do backend
- financeiro do parceiro lendo saldo, repasses e extrato pela API
- financeiro do admin lendo volume, comissoes e repasses pela API
- dashboard do entregador lendo dados reais do backend
- perfil do entregador lendo e salvando dados reais pela API
- ganhos, disponibilidade e documentos do entregador ligados ao banco
- driver portal em modo API-first nas telas principais
- suporte do parceiro ligado a tickets reais
- suporte do admin ligado a fila real de atendimento
- suporte do entregador ligado a tickets reais
- landing publica ligada a categorias e metricas da API
- landing publica com captura real de leads de parceiro e entregador
- admin com aprovacao e rejeicao reais nas filas de parceiro e entregador
- mensagens do parceiro detalhadas por protocolo com thread completa
- equipe da loja com membros, perfis e status ligados ao backend
- notificacoes operacionais no portal do parceiro
- notificacoes operacionais no portal do entregador

Proxima etapa recomendada:

- iniciar a Sprint 11 da Fase 9
- unificar autenticacao da equipe da loja
- abrir configuracoes administrativas da plataforma
- evoluir notificacoes para tempo real
