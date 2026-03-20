# Decisão de stack da Fox Platform

## Objetivo da Fase 1

Fechar a fundação técnica do novo sistema da Fox Delivery sem depender do 6amMart.

## Stack escolhida

### Backend

- linguagem: PHP 8.3
- framework-alvo: Laravel 12 em modo API-first
- padrão interno: modular por domínio

### Frontend web

- framework-alvo: Next.js com TypeScript
- apps separados por público:
  - `admin`
  - `partner-portal`
  - `driver-portal`
  - `landing`

### Banco e infraestrutura

- banco principal: PostgreSQL 16
- cache e fila: Redis 7
- storage de arquivos: MinIO em desenvolvimento e S3 em produção
- e-mail local: Mailpit
- ambiente local: Docker Compose

## Decisões de arquitetura

### Monorepo

O sistema será mantido em um monorepo com cinco apps e três packages compartilhados.

### API-first

A regra de negócio nasce na API.

Isso garante:

- independência entre painéis
- consistência entre admin, parceiro, entregador e landing
- escalabilidade para apps móveis no futuro

### Separação por domínio

Os principais domínios são:

- identity
- stores
- catalog
- orders
- logistics
- finance
- marketing
- support
- analytics

### Autenticação própria

O sistema terá autenticação própria, com:

- access token
- refresh token
- sessões revogáveis
- recuperação de senha
- base pronta para 2FA futuro

### RBAC desde o início

A autorização será feita por:

- papéis
- permissões por módulo
- escopo por contexto de negócio

Exemplos:

- um `partner_owner` pode gerir sua loja inteira
- um `partner_staff` pode ver pedidos, mas não financeiro
- um `admin_financeiro` pode acessar repasses, mas não aprovar campanhas

## Convenções de código

- idioma de negócio: português do Brasil
- nomes técnicos: inglês para código e tabelas
- datas sempre em UTC no banco
- timezone de operação padrão: `America/Sao_Paulo`
- IDs com UUID para entidades críticas
- logs e auditoria ativados desde cedo

## O que não entra na Fase 1

- telas finais de produção
- BI avançado
- automações
- aplicativo nativo
- CRM comercial
- integrações externas complexas

