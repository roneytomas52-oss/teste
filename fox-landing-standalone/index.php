<?php

declare(strict_types=1);

ob_start();
?>
<section class="home-hub-hero">
    <div class="container home-hub-grid">
        <div class="home-hub-copy">
            <span class="eyebrow">Fox Delivery</span>
            <h1>Pe&ccedil;a comida, mercado, farm&aacute;cia e conveni&ecirc;ncia em minutos.</h1>
            <p>Tudo em um s&oacute; lugar, com mais praticidade para pedir, vender e entregar.</p>

            <div class="home-hub-actions" id="apps">
                <a class="btn" href="#categorias" data-track="home_order_cta_click" data-track-component="hero">Pedir agora</a>
                <a class="btn ghost" href="./cadastro-parceiros.php" data-track="home_partner_cta_click" data-track-component="hero">Seja parceiro</a>
            </div>
        </div>
    </div>
</section>

<section class="container section home-section">
    <div class="section-head center home-centered-head">
        <span class="eyebrow">Acessos principais</span>
        <h2>Escolha seu acesso</h2>
        <p>Cliente, parceiro e entregador com caminhos claros desde o primeiro clique.</p>
    </div>

    <div class="home-profile-grid">
        <article class="home-profile-card surface-card">
            <small>Cliente</small>
            <h3>Pedir na plataforma</h3>
            <p>Acesse restaurante, mercado, farm&aacute;cia e conveni&ecirc;ncia em uma jornada mais r&aacute;pida.</p>
            <a class="btn" href="#categorias" data-track="home_customer_entry_click" data-track-component="profile_card">Fazer pedido</a>
        </article>

        <article class="home-profile-card surface-card">
            <small>Parceiro</small>
            <h3>Vender na Fox Delivery</h3>
            <p>Cadastre sua loja e entre na plataforma com um fluxo comercial mais organizado.</p>
            <a class="btn ghost" href="./cadastro-parceiros.php" data-track="home_partner_entry_click" data-track-component="profile_card">Seja parceiro</a>
        </article>

        <article class="home-profile-card delivery surface-card">
            <small>Entregador</small>
            <h3>Entregar com a Fox</h3>
            <p>Inicie seu cadastro com um fluxo pr&oacute;prio para modalidade, perfil e documenta&ccedil;&atilde;o.</p>
            <a class="btn ghost" href="./cadastro-entregador.php" data-track="home_delivery_entry_click" data-track-component="profile_card">Seja entregador</a>
        </article>
    </div>
</section>

<section class="container section home-section" id="categorias">
    <div class="section-head center home-centered-head">
        <span class="eyebrow">Categorias</span>
        <h2>Pe&ccedil;a por categoria</h2>
        <p>Os principais tipos de pedido da Fox Delivery em acessos diretos e f&aacute;ceis de entender.</p>
    </div>

    <div class="home-category-grid home-category-grid-compact">
        <article class="home-category-card surface-card">
            <small>Refei&ccedil;&otilde;es</small>
            <strong>Restaurante</strong>
            <p>Pratos, lanches e refei&ccedil;&otilde;es do dia em um acesso r&aacute;pido.</p>
        </article>

        <article class="home-category-card surface-card">
            <small>Compras</small>
            <strong>Mercado</strong>
            <p>Compras recorrentes com mais praticidade para a rotina.</p>
        </article>

        <article class="home-category-card surface-card">
            <small>Cuidados</small>
            <strong>Farm&aacute;cia</strong>
            <p>Produtos essenciais em uma jornada objetiva e organizada.</p>
        </article>

        <article class="home-category-card surface-card">
            <small>Dia a dia</small>
            <strong>Conveni&ecirc;ncia</strong>
            <p>Itens r&aacute;pidos para diferentes momentos do dia.</p>
        </article>
    </div>
</section>

<section class="section section-soft home-process-section">
    <div class="container">
        <div class="section-head center home-centered-head">
            <span class="eyebrow">Como funciona</span>
            <h2>Uma jornada simples em 3 passos</h2>
            <p>Do primeiro clique ao acompanhamento da opera&ccedil;&atilde;o, tudo fica mais claro para cada perfil.</p>
        </div>

        <div class="home-process-grid home-process-grid-compact">
            <article class="surface-card home-process-card">
                <span class="home-process-number">1</span>
                <strong>Escolha o que precisa</strong>
                <p>Veja as categorias ou entre direto na jornada certa.</p>
            </article>

            <article class="surface-card home-process-card">
                <span class="home-process-number">2</span>
                <strong>Inicie sua a&ccedil;&atilde;o</strong>
                <p>Pe&ccedil;a, cadastre sua loja ou siga para o cadastro de entregador.</p>
            </article>

            <article class="surface-card home-process-card">
                <span class="home-process-number">3</span>
                <strong>Acompanhe com clareza</strong>
                <p>Conte com orienta&ccedil;&atilde;o, suporte e continuidade dentro da plataforma.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section home-section">
    <div class="section-head center home-centered-head">
        <span class="eyebrow">Confian&ccedil;a</span>
        <h2>Uma estrutura pensada para operar com clareza</h2>
        <p>A Fox Delivery organiza a entrada de clientes, parceiros e entregadores em uma experi&ecirc;ncia mais objetiva.</p>
    </div>

    <div class="home-trust-grid">
        <article class="home-trust-card surface-card">
            <strong>Acessos separados</strong>
            <p>Cliente, parceiro e entregador entram na jornada certa desde o in&iacute;cio.</p>
        </article>

        <article class="home-trust-card surface-card">
            <strong>Categorias claras</strong>
            <p>Restaurante, mercado, farm&aacute;cia e conveni&ecirc;ncia com leitura mais r&aacute;pida.</p>
        </article>

        <article class="home-trust-card surface-card">
            <strong>Suporte e opera&ccedil;&atilde;o</strong>
            <p>Ajuda, contato e cadastros conectados no mesmo ecossistema da plataforma.</p>
        </article>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Início';
$current = 'home';
require __DIR__ . '/includes/layout.php';
