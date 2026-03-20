# Dominios de banco da Fox Platform

## 1. Identity

Tabelas previstas:

- users
- roles
- permissions
- role_permissions
- user_roles
- sessions
- password_resets
- audit_logs

## 2. Partners and Stores

Tabelas previstas:

- vendors
- stores
- store_settings
- store_addresses
- store_documents
- store_hours
- store_status_history
- vendor_employees
- employee_permissions

## 3. Catalog

Tabelas previstas:

- categories
- subcategories
- products
- product_images
- product_variants
- product_addons
- product_stocks
- product_status_history

## 4. Orders

Tabelas previstas:

- orders
- order_items
- order_status_logs
- order_payments
- order_notes
- order_cancellations
- refunds

## 5. Logistics

Tabelas previstas:

- drivers
- driver_documents
- driver_vehicles
- driver_status_history
- delivery_assignments
- delivery_tracking_points
- delivery_earnings

## 6. Finance

Tabelas previstas:

- wallets
- wallet_transactions
- payouts
- payout_methods
- settlement_cycles
- commissions
- expenses
- invoices

## 7. Marketing

Tabelas previstas:

- coupons
- banners
- campaigns
- campaign_targets
- promotions

## 8. Support

Tabelas previstas:

- tickets
- ticket_messages
- conversations
- notifications

## 9. Analytics

Tabelas previstas:

- metric_snapshots
- dashboard_widgets
- event_logs

## Relacoes principais

- um vendor pode ter uma ou mais stores
- uma store possui varios products
- uma store possui varios orders
- uma store possui varios vendor_employees
- um driver possui varios delivery_assignments
- uma order possui varios order_items
- uma order possui historico de status
- uma store possui configuracoes, horarios e documentos proprios

## Regras importantes

- todas as entidades criticas devem ter `status`
- mudancas sensiveis devem gerar historico
- financeiro precisa ser conciliavel
- pedidos nao devem depender de calculos soltos no frontend
- permissoes devem existir desde o inicio
