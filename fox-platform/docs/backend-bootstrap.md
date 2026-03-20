# Bootstrap técnico do backend

## App principal

`apps/api`

## Diretriz

O backend da Fox Platform será a fonte única de verdade para regra de negócio.

## Estrutura de pastas definida para a Fase 1

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

## Camadas

### Domain

Contém:

- entidades
- value objects
- contratos
- invariantes
- regras de negócio puras

### Application

Contém:

- casos de uso
- serviços de aplicação
- DTOs
- orquestração entre domínio e infraestrutura

### Infrastructure

Contém:

- repositórios
- persistência
- fila
- cache
- adapters externos

### Interfaces/Http

Contém:

- controllers
- requests
- transformers
- mapeamento HTTP

## Módulos do backend para a Fase 1

- identity
- access-control
- stores
- drivers
- audit
- platform-core

## Grupos de rota da API

- `/api/v1/auth`
- `/api/v1/admin`
- `/api/v1/partner`
- `/api/v1/driver`
- `/api/v1/public`

## Contratos mínimos para a próxima fase

- login
- refresh token
- logout
- me
- dashboard base
- pedidos base
- catálogo base

## Regra de organização

Nenhuma regra crítica deve nascer acoplada a um painel específico.

Exemplo correto:

- API calcula permissão de edição de pedido

Exemplo incorreto:

- front do parceiro decide sozinho se pode editar pedido

