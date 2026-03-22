# Fase 9 - Sprint 13: Detalhe de pedidos e visao operacional

## Objetivo

Fechar a leitura operacional completa de pedidos para parceiro e admin, eliminando pontos cegos entre lista e detalhe e garantindo validacao real via smoke test.

## O que entrou

### Backend

- novos casos de uso:
  - `GetPartnerOrderDetail`
  - `GetAdminOrderDetail`
- novas rotas:
  - `GET /api/v1/partner/orders/{order_id}`
  - `GET /api/v1/admin/orders/{order_id}`
- repositórios reais atualizados para montar:
  - cabecalho do pedido
  - itens do pedido
  - linha do tempo operacional
- ajuste final no repositorio do admin para suportar `formatDateTime()` no detalhe

### Partner Portal

- nova tela:
  - `order-detail.html`
- lista de pedidos agora leva para o detalhe real do pedido
- tela exibe:
  - cliente
  - endereco
  - entregador
  - pagamento
  - subtotal, entrega e total
  - itens
  - timeline

### Admin

- nova tela:
  - `order-detail.html`
- lista operacional do admin agora inclui coluna de acoes com `Detalhar`
- tela exibe:
  - loja
  - cliente
  - pagamento
  - itens
  - historico operacional completo

### UI

- novos estilos para linhas de item e timeline em:
  - `partner-portal.css`
  - `admin.css`
- estados de feedback visualizados com `fx-note[data-tone]`

### Smoke test

O smoke test passou a validar tambem:

- `partner.order-detail`
- `admin.order-detail`

## Validacao

Validacoes executadas:

- `php -l` nos repositórios e nas rotas alteradas
- migrations e seeds reaplicados sem erro
- servidor PHP local ativo em `127.0.0.1:8099`
- `php scripts/smoke-test.php` concluido com sucesso

Resumo validado:

- health
- auth admin, partner e driver
- areas principais dos tres portais
- categorias e metricas publicas
- leads publicos
- detalhe real de pedido no parceiro
- detalhe real de pedido no admin

## Estado apos a sprint

O `fox-platform` agora tem visao operacional completa de pedidos nas duas pontas mais importantes do backoffice:

- parceiro acompanha o pedido em profundidade
- admin consegue inspecionar o mesmo pedido com contexto de loja, cliente, pagamento, itens e timeline

Isso reduz risco de operacao cega e prepara o terreno para a proxima sprint de acoes administrativas e fluxos de atendimento mais completos.
