# Roadmap do MVP da Fox Platform

## Fase 1 - Fundacao tecnica [concluida]

- definir stack final
- criar backend proprio
- criar banco proprio
- criar autenticacao
- criar base de roles e permissions

## Fase 2 - Shell dos apps [concluida]

- shell do admin
- shell do partner portal
- shell do driver portal
- shell da landing
- design system base

## Fase 3 - Partner Portal MVP [concluida]

- login
- dashboard
- pedidos
- produtos
- estoque
- loja
- horarios
- perfil

## Fase 4 - Financeiro e relatorios [concluida]

- extrato
- repasses
- resumo financeiro
- relatorios basicos

## Fase 5 - Admin MVP [concluida]

- aprovacao de parceiros
- aprovacao de entregadores
- visao de pedidos
- controle operacional
- controle financeiro basico

## Fase 6 - Driver Portal MVP [concluida]

- login
- perfil
- documentos
- ganhos
- disponibilidade

## Fase 7 - Suporte e refinamento [concluida]

- chamados
- ajuda
- mensagens
- auditoria
- analytics

## Fase 8 - Integracao real viavel [concluida]

- camada mock versionada
- SDK compartilhado
- sessao local
- login funcional no navegador
- hidratacao das telas principais

## Fase 9 - Backend executavel [em andamento]

- Sprint 1 concluida:
  - bootstrap HTTP do backend
  - autenticacao funcional
  - rotas base de auth e `me`
  - seeds iniciais e banco preparado
- Sprint 2 concluida:
  - Partner Core ligado no backend
  - perfil real do parceiro
  - loja, horarios e documentos com rotas reais
  - SDK do parceiro em modo API-first com fallback local
- Sprint 3 concluida:
  - catalogo real ligado ao backend
  - criacao e edicao real de produto
  - ajuste de estoque por produto
  - telas de catalogo e estoque conectadas
- Sprint 4 concluida:
  - dashboard do parceiro ligado ao backend real
  - pedidos do parceiro ligados ao backend real
  - alteracao de status do pedido no portal do parceiro
  - dashboard do admin ligado ao backend real
  - pedidos do admin ligados ao backend real
  - filas de aprovacao do admin ligadas ao backend real
- Sprint 5 concluida:
  - financeiro real do parceiro
  - financeiro real do admin
  - repasses, conta bancaria e extrato ligados ao banco
  - telas financeiras consumindo a API real
- Sprint 6 concluida:
  - dashboard real do entregador
  - perfil real do entregador com atualizacao pela API
  - ganhos, disponibilidade e documentos ligados ao banco
  - driver portal consumindo a API real nas telas principais
- Sprint 7 concluida:
  - suporte real de parceiro, admin e entregador
  - tickets persistidos no banco
  - telas de suporte consumindo a API
- Sprint 8 concluida:
  - rotas publicas reais para categorias, metricas e leads
  - landing consumindo categorias e metricas da API
  - captura publica de lead para parceiro e entregador
- Sprint 9 concluida:
  - aprovacao real de parceiros no admin
  - aprovacao real de entregadores no admin
  - filas do admin com acoes operacionais pela API
- Sprint 10 concluida:
  - mensagens detalhadas por ticket no portal do parceiro
  - equipe e permissoes da loja ligadas ao backend
  - notificacoes operacionais no portal do parceiro e do entregador
- Proximo foco:
  - autenticacao unificada da equipe da loja
  - configuracoes administrativas da plataforma
  - notificacoes em tempo real e historico operacional

## Entregavel minimo para primeira versao

- landing publica
- login de parceiro
- dashboard da loja
- pedidos
- produtos
- configuracoes da loja
- financeiro basico
- painel admin basico

## O que fica para depois

- BI avancado
- automacoes
- app nativo
- CRM comercial
- campanhas avancadas
- multiempresa
