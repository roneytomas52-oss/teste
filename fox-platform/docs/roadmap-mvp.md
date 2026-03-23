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

## Fase 9 - Backend executavel [concluida para operacao local]

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
- Sprint 11 concluida:
  - autenticacao unificada da equipe da loja com contexto de acesso por store
  - permissoes aplicadas no partner portal para dono, gerente e equipe
  - configuracoes administrativas da plataforma no admin
  - notificacoes do parceiro e do entregador com polling de atualizacao
  - fallback do SDK ajustado para ignorar respostas externas nao compatíveis com a API da Fox Platform
  - validacao de runtime com health check real da API
- Sprint 12 concluida:
  - runtime local endurecido com router PHP valido para a API
  - servidor estatico unico para os apps web da plataforma
  - smoke test completo validado contra a API real
  - contrato de admin settings estabilizado para MySQL
  - leitura de documentos do entregador alinhada ao schema real
- Sprint 13 concluida:
  - detalhe operacional de pedidos para parceiro
  - detalhe operacional de pedidos para admin
  - lista de pedidos com navegacao para detalhe nas duas interfaces
  - smoke test expandido e validado contra os endpoints novos
- Sprint 14 concluida:
  - detalhe do ticket de suporte no admin
  - resposta do admin ao ticket pela API
  - atualizacao de status do ticket pela API
  - fila do suporte ligada a tela de atendimento
  - smoke test expandido e validado contra os endpoints de suporte do admin
- Sprint 15 concluida:
  - acoes operacionais do admin em pedidos
  - atualizacao de status de pedido pela API
  - observacao interna de pedido pela API
  - detalhe do pedido no admin com formularios operacionais
  - smoke test expandido e validado contra `admin.order.status` e `admin.order.note`
- Sprint 16 concluida:
  - analise detalhada de aprovacao de parceiros no admin
  - analise detalhada de aprovacao de entregadores no admin
  - historico persistido de revisao administrativa por cadastro
  - decisao com observacao para parceiro e entregador pela API
  - smoke test expandido e validado contra os fluxos de detalhe e decisao de aprovacao
- Sprint 17 concluida:
  - gestao de acessos internos do admin pela API
  - tela de permissoes e acessos administrativos no painel interno
  - notificacoes administrativas com leitura e acompanhamento por tela dedicada
  - seed complementar de equipe interna e notificacoes do admin
  - smoke test expandido e validado contra `admin.notifications` e `admin.access.*`
 - Sprint 18 concluida:
   - analytics do admin ligado ao backend real
   - relatorios administrativos consolidados via API
   - nova tela `reports.html` integrada ao painel interno
   - fallback do admin alinhado ao novo contrato de analytics e relatorios
   - smoke test expandido e validado contra `admin.analytics` e `admin.reports`
 - Sprint 19 concluida:
   - descoberta publica de lojas via API
   - detalhe publico da loja com catalogo ativo
   - criacao de pedido guest pela API publica
   - novas telas `stores.html` e `store.html` na landing
   - smoke test expandido e validado contra `public.stores`, `public.store-detail` e `public.order.create`
- Sprint 20 concluida:
  - rastreio publico de pedido pela API
  - nova tela `track.html` na landing
  - criacao e consulta do pedido cobertas no fluxo publico do cliente
  - smoke test expandido e validado contra `public.order.tracking`

## Estado do MVP

- operacao local concluida para:
  - landing publica
  - descoberta de lojas
  - detalhe de loja
  - criacao de pedido guest
  - acompanhamento publico do pedido
  - portal do parceiro
  - portal do entregador
  - painel admin
  - API propria
- validacao tecnica executada com:
  - `php -l`
  - `node --check`
  - smoke test completo
  - subida HTTP local da API e do servidor estatico

## Proximo bloco fora do MVP local

- pagamento real
- dispatch/logistica ao vivo
- tracking em tempo real
- deploy com dominio, SSL e observabilidade
- filtros/exportacoes administrativas mais profundas

## Entregavel minimo para primeira versao

- landing publica
- login de parceiro
- dashboard da loja
- pedidos
- produtos
- configuracoes da loja
- financeiro basico
- painel admin basico
- descoberta publica de lojas
- detalhe de loja e criacao de pedido guest
- rastreio publico do pedido

## O que fica para depois

- BI avancado
- automacoes
- app nativo
- CRM comercial
- campanhas avancadas
- multiempresa
