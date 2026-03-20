# Superficie inicial de APIs

## Auth

- POST `/auth/login`
- POST `/auth/logout`
- POST `/auth/refresh`
- POST `/auth/forgot-password`
- POST `/auth/reset-password`
- GET `/auth/me`

## Admin

- GET `/admin/dashboard`
- GET `/admin/vendors`
- GET `/admin/stores`
- GET `/admin/drivers`
- GET `/admin/orders`
- GET `/admin/reports`
- GET `/admin/finance`
- GET `/admin/support`
- GET `/admin/audit`
- GET `/admin/analytics`

## Partner Portal

### dashboard

- GET `/partner/dashboard`

### store

- GET `/partner/store`
- PUT `/partner/store`
- PUT `/partner/store/status`
- PUT `/partner/store/hours`
- POST `/partner/store/documents`

### products

- GET `/partner/products`
- POST `/partner/products`
- GET `/partner/products/{id}`
- PUT `/partner/products/{id}`
- DELETE `/partner/products/{id}`
- PUT `/partner/products/{id}/status`
- PUT `/partner/products/{id}/stock`

### categories and addons

- GET `/partner/categories`
- GET `/partner/addons`
- POST `/partner/addons`
- PUT `/partner/addons/{id}`
- DELETE `/partner/addons/{id}`

### orders

- GET `/partner/orders`
- GET `/partner/orders/{id}`
- PUT `/partner/orders/{id}/status`
- POST `/partner/orders/{id}/note`
- GET `/partner/orders/export`

### promotions

- GET `/partner/coupons`
- POST `/partner/coupons`
- PUT `/partner/coupons/{id}`
- DELETE `/partner/coupons/{id}`

### finance

- GET `/partner/finance/summary`
- GET `/partner/finance/transactions`
- GET `/partner/finance/payouts`
- POST `/partner/finance/payout-request`
- PUT `/partner/finance/bank-account`

### reports

- GET `/partner/reports/sales`
- GET `/partner/reports/orders`
- GET `/partner/reports/products`
- GET `/partner/reports/finance`

### messages, support and help

- GET `/partner/messages`
- GET `/partner/support`
- POST `/partner/support/tickets`
- GET `/partner/help`

### staff

- GET `/partner/staff`
- POST `/partner/staff`
- PUT `/partner/staff/{id}`
- DELETE `/partner/staff/{id}`

### profile

- GET `/partner/profile`
- PUT `/partner/profile`
- PUT `/partner/profile/password`

## Driver Portal

- GET `/driver/profile`
- PUT `/driver/profile`
- POST `/driver/documents`
- GET `/driver/earnings`
- GET `/driver/orders`
- GET `/driver/history`
- POST `/driver/availability`
- POST `/driver/payout-request`
- GET `/driver/support`

## Support

- GET `/support/tickets`
- POST `/support/tickets`
- GET `/support/tickets/{id}`
- POST `/support/tickets/{id}/message`

## Observacoes

- o sistema deve nascer API-first
- o frontend nao deve depender de renderizacao Blade
- endpoints devem ser versionados desde o inicio
- durante a fase atual, os apps web consomem uma camada mock versionada em `apps/api/mock/v1`
