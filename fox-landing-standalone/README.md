# Fox Landing Standalone (independente)

Esta pasta foi criada para você **baixar separada** e subir em qualquer hospedagem (ex: FileZilla), mantendo sincronização com o 6amMart.

## O que sincroniza com o 6amMart

- **Leitura de configurações** no mesmo banco:
  - `business_settings` (nome, logo, telefone, email, endereço)
  - `data_settings` (links de download app)
- **Contato** grava direto na tabela `contacts`.
- **Cadastro de loja e entregador** usa os fluxos oficiais do 6amMart:
  - `vendor/apply`
  - `deliveryman/apply`

## Como publicar em outra hospedagem

1. Copie a pasta `fox-landing-standalone` para seu host.
2. Duplique `.env.example` para `.env`.
3. Configure no `.env`:
   - `SIXAMMART_BASE_URL`
   - dados do mesmo banco usado pelo 6amMart (`DB_*`).
4. Garanta PHP 8+ com extensão PDO MySQL habilitada.
5. Acesse `index.php`.

## Estrutura

- `index.php`, `sobre.php`, `contato.php`, `cadastro-loja.php`, `cadastro-entregador.php`
- `includes/` (bootstrap, conexão e layout)
- `assets/style.css`

## Observação

Para ficar visualmente 100% igual às prints (mesmas ilustrações/imagens), basta substituir os blocos/arte pelos seus arquivos finais em `assets/` mantendo a estrutura pronta.
