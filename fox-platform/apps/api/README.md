# API

Backend principal da Fox Platform.

## Papel no produto

Responsável por:

- autenticação
- autorização
- regras de negócio
- persistência
- permissões
- auditoria
- integrações

## Stack-alvo

- PHP 8.3
- Laravel 12 API-first
- PostgreSQL
- Redis
- MinIO/S3

## Estrutura inicial

```text
apps/api/
  config/
  routes/
  database/
    migrations/
    seeders/
  src/
    Domain/
    Application/
    Infrastructure/
    Interfaces/
      Http/
```

## Entregas já criadas na Fase 1

- configuração-base do app
- configuração-base de auth
- mapa inicial de endpoints
- migrations de identidade, perfis e auditoria
- seed inicial de papéis e permissões

