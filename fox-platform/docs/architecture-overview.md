# Arquitetura geral da Fox Platform

## Visao

A Fox Platform sera o sistema central da Fox Delivery.

Ela sera composta por cinco aplicacoes principais:

1. `api`
2. `admin`
3. `partner-portal`
4. `driver-portal`
5. `landing`

## Objetivo de cada aplicacao

### API

Camada central de negocio.

Responsabilidades:

- autenticacao
- autorizacao
- regras de negocio
- pedidos
- catalogo
- financeiro
- relatorios
- suporte a integracoes

### Admin

Painel interno da Fox Delivery.

Responsabilidades:

- aprovar lojas
- aprovar entregadores
- operar pedidos
- configurar a plataforma
- visualizar financeiro global
- acompanhar relatorios
- moderar conteudo e campanhas

### Partner Portal

Portal da loja parceira.

Responsabilidades:

- dashboard da loja
- catalogo
- pedidos
- financeiro
- equipe
- configuracoes da loja
- relatorios

### Driver Portal

Portal do entregador.

Responsabilidades:

- perfil
- documentos
- ganhos
- carteira
- historico operacional
- suporte

### Landing

Camada publica da marca.

Responsabilidades:

- aquisicao
- posicionamento de marca
- captacao de parceiros
- captacao de entregadores
- distribuicao das jornadas

## Papeis do sistema

### Operacao interna

- super_admin
- admin_operacional
- admin_financeiro
- admin_comercial
- suporte

### Parceiros

- partner_owner
- partner_manager
- partner_staff

### Entregadores

- driver

### Publico

- visitante

## Limites do sistema

O novo sistema nao deve depender de:

- banco do 6amMart
- guards do 6amMart
- controllers do 6amMart
- views Blade do 6amMart
- rotas do 6amMart

O 6amMart serve apenas como referencia funcional.

## Decisao de arquitetura

### Separacao por app

Cada interface principal tera seu proprio app.

Vantagens:

- independencia de deploy
- controle de UX por publico
- escalabilidade
- manutencao mais limpa

### Camada compartilhada

`packages/core`

- tipos
- contratos
- enums
- regras compartilhadas

`packages/ui`

- design system
- componentes base
- tokens visuais

`packages/sdk`

- clientes HTTP internos
- normalizacao de respostas
- helpers de autenticacao

## Dominios funcionais

- identity
- stores
- catalog
- orders
- logistics
- finance
- marketing
- support
- analytics

## Regras de produto

- cada publico entra por uma jornada separada
- o parceiro opera por tarefa, nao por pagina institucional
- o admin controla aprovacao, risco, financeiro e auditoria
- o entregador tem fluxo proprio e independente
- todo o sistema precisa suportar crescimento sem acoplamento com o legado

## Premissas para a fase de implementacao

- backend proprio
- banco proprio
- autenticacao propria
- permissao por papel
- API-first
- layout moderno de operacao
- rastreio de eventos e auditoria desde cedo
