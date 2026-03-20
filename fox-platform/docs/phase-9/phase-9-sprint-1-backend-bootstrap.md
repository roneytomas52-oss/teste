# Sprint 1 da Fase 9 - Backend bootstrap e autenticacao

## Status

Em implementacao.

## Entrega desta sprint

- bootstrap HTTP em PHP puro dentro de `apps/api`
- autoload proprio
- leitura de `.env`
- container simples
- roteador HTTP inicial
- request/response JSON padrao
- auth com login, logout, refresh, forgot, reset e `me`
- PDO para PostgreSQL
- tokens de acesso assinados
- refresh sessions persistidas em banco
- migrations complementares de identidade
- seed inicial de usuarios reais para admin, parceiro e entregador
- scripts de `migrate` e `seed` sem dependencia de framework

## Endpoints abertos nesta sprint

- `GET /health`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/refresh`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `GET /api/v1/auth/me`

## Credenciais iniciais previstas no seed

Senha inicial: `password`

- admin: `admin@foxplatform.com`
- parceiro: `parceiro@foxdelivery.com.br`
- entregador: `entregador@foxdelivery.com.br`

## Dependencia do ambiente

Para executar localmente, esta sprint ainda depende de:

- PHP instalado
- extensao PDO PgSQL
- PostgreSQL acessivel

Sem isso, a base de codigo pode ser preparada, mas nao executada neste terminal.
