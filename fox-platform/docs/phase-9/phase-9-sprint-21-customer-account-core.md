# Fase 9 - Sprint 21

## Escopo

Fechar o lado autenticado do cliente na Fox Platform, cobrindo:

- cadastro publico de cliente
- login do cliente
- perfil e conta autenticada
- historico de pedidos
- detalhe do pedido autenticado
- criacao de pedido autenticado vinculada a conta

## Backend

Entregue:

- migration de customer core em `apps/api/database/migrations/2026_03_20_000019_customer_core.sql`
- seed de cliente demo e role `customer` em `apps/api/database/seeders/2026_03_22_phase_9_customer_core.sql`
- repositorio do cliente em `apps/api/src/Infrastructure/Persistence/PdoCustomerPortalRepository.php`
- casos de uso do cliente em `apps/api/src/Application/Customer`
- controller do cliente em `apps/api/src/Interfaces/Http/Controllers/CustomerController.php`
- cadastro publico do cliente via `PublicLandingController`
- wiring no container e rotas autenticadas/publicas

## Frontend

Entregue:

- `customer-login.html`
- `customer-register.html`
- `account.html`
- `my-orders.html`
- `customer-order.html`
- `customer-app.js`
- integracao do checkout publico para usar pedido autenticado quando a sessao for de cliente
- cabecalho publico atualizado para refletir sessao do cliente

## Validacao

Executado com sucesso:

- `php -l`
- `node --check`
- `php apps/api/scripts/migrate.php`
- `php apps/api/scripts/seed.php`
- `php scripts/smoke-test.php`

Cobertura adicionada ao smoke:

- `public.customer-register`
- `auth.login.customer`
- `auth.me.customer`
- `customer.profile`
- `customer.profile.update`
- `customer.orders.before`
- `customer.order.create`
- `customer.orders.after`
- `customer.order.detail`

## Resultado

A plataforma agora opera localmente com quatro jornadas reais:

- cliente
- parceiro
- entregador
- admin

No lado do cliente, ja e possivel:

- criar conta
- entrar
- atualizar perfil
- criar pedido autenticado
- consultar historico
- abrir detalhe do pedido
- rastrear pedido por numero
