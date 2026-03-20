# Fundação de autenticação e RBAC

## Objetivo

Garantir que a Fox Platform nasça com uma camada própria de identidade, autorização e rastreabilidade.

## Modelo de identidade

### Entidade central

`users`

Essa tabela representa qualquer pessoa autenticável na plataforma:

- admins
- parceiros
- funcionários de loja
- entregadores

## Sessões

O modelo de sessão será baseado em:

- access token curto
- refresh token longo
- sessão revogável por dispositivo

Campos mínimos por sessão:

- usuário
- identificador do token
- IP
- user agent
- data de expiração
- data de revogação

## Perfis por contexto

Um mesmo usuário pode ter mais de um contexto operacional, por isso os perfis ficam separados:

- `admin_profiles`
- `partner_accounts`
- `stores`
- `driver_profiles`

## Papéis padrão

### Operação interna

- `super_admin`
- `admin_operacional`
- `admin_financeiro`
- `admin_comercial`
- `suporte`

### Parceiros

- `partner_owner`
- `partner_manager`
- `partner_staff`

### Entregadores

- `driver`

## Módulos de permissão

- `dashboard`
- `orders`
- `catalog`
- `inventory`
- `marketing`
- `finance`
- `store`
- `team`
- `reviews`
- `support`
- `reports`
- `settings`
- `approvals`

## Ações base

- `view`
- `create`
- `update`
- `delete`
- `manage`
- `approve`
- `export`

## Estratégia de autorização

### Camada 1: papel

Determina o tipo de operação permitida.

### Camada 2: permissão

Determina o que o usuário pode fazer em cada módulo.

### Camada 3: escopo

Limita a ação ao contexto correto:

- loja específica
- conta parceira específica
- operação global admin

## Regras mínimas da Fase 1

- todo login gera sessão rastreável
- toda sessão pode ser revogada
- toda ação relevante pode ser auditada
- papéis e permissões ficam no banco
- autorização não depende de texto hardcoded na UI

## Auditoria

Toda ação crítica deverá poder gerar registro de auditoria, como:

- login
- troca de senha
- aprovação de cadastro
- alteração de preço
- alteração de status de pedido
- alteração de conta bancária
- solicitação de saque

