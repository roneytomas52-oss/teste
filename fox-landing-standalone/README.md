# Fox Landing Standalone (independente)

Pasta criada para publicar separadamente (ex.: FileZilla) em qualquer hospedagem PHP, mantendo sincronização com o **mesmo painel/banco do 6amMart**.

## Sincronização com painel/admin

- **Botão "Peça agora"**: lê `business_settings.landing_page_links.web_app_url`.
- **Menu topo**: mantém estrutura da landing e usa URLs sincronizadas para cadastro:
  - Loja: `data_settings(type=flutter_landing_page,key=join_seller_button_url)`
  - Entregador: `data_settings(type=flutter_landing_page,key=join_delivery_man_button_url)`
- **Seção "Tudo em um só lugar"**: lista módulos ativos da tabela `modules` (nome + ícone).
- **Seção "Como funciona"**: usa cards da tabela `admin_features` (imagem + título + subtítulo).
- **Rodapé**: dados de contato em `business_settings` e redes em `social_media`.
- **Contato**: grava mensagens na tabela `contacts`.

## Como publicar em outra hospedagem

1. Copie `fox-landing-standalone` para seu host.
2. Renomeie `.env.example` para `.env`.
3. Configure `SIXAMMART_BASE_URL` e `DB_*` com o mesmo banco do 6amMart.
4. Garanta PHP 8+ com PDO MySQL.
5. Abra `index.php`.
