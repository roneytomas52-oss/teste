# API

Backend principal da Fox Platform.

## Papel no produto

Responsavel por:

- autenticacao
- autorizacao
- regras de negocio
- persistencia
- permissoes
- auditoria
- integracoes

## Stack-alvo

- PHP 8.3
- Laravel 12 API-first como alvo de maturidade
- PostgreSQL
- Redis
- MinIO/S3

## Estrutura inicial

```text
apps/api/
  bootstrap/
  config/
  public/
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

## Entregas ja criadas

- configuracao-base do app
- configuracao-base de auth
- mapa inicial de endpoints
- migrations de identidade, perfis e auditoria
- seed inicial de papeis e permissoes
- bootstrap HTTP da Sprint 1 da Fase 9
- container e roteador base
- auth real por token assinado
- refresh sessions persistidas
- endpoints de `health` e `auth`
- seeds iniciais de admin, parceiro e entregador
- Partner Core inicial
- endpoints reais de perfil, loja, horarios e documentos do parceiro
- bridge do SDK preparada para consumir a API HTTP com fallback local

## Observacao importante

O backend desta fase foi estruturado em PHP puro para sair do mock mesmo sem `composer` e `laravel` disponiveis neste ambiente. A arquitetura continua preparada para evoluir depois para um stack Laravel API-first, sem perder os contratos e modulos definidos.
