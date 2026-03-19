<?php

declare(strict_types=1);

ob_start();
?>
<section class="home-hub-hero">
    <div class="container home-hub-grid">
        <div class="home-hub-copy">
            <span class="eyebrow">Fox Delivery</span>
            <h1>Peca restaurante, mercado, farmacia e conveniencia em minutos.</h1>
            <p>Uma plataforma mais simples para pedir, vender e entregar com mais rapidez e clareza.</p>

            <div class="home-hub-actions" id="apps">
                <a class="btn" href="#categorias" data-track="home_order_cta_click" data-track-component="hero">Pedir agora</a>
                <a class="btn ghost" href="./cadastro-parceiros.php" data-track="home_partner_cta_click" data-track-component="hero">Seja parceiro</a>
            </div>

            <div class="home-hub-points">
                <span>Restaurante</span>
                <span>Mercado</span>
                <span>Farmacia</span>
                <span>Conveniencia</span>
            </div>
        </div>

        <aside class="home-hub-highlight surface-card">
            <small>Plataforma Fox Delivery</small>
            <strong>Cliente, parceiro e entregador em jornadas separadas e mais claras.</strong>
            <p>Escolha o perfil certo e siga para a proxima etapa com uma experiencia mais organizada.</p>
            <div class="home-hub-highlight-points">
                <span>Pedidos</span>
                <span>Lojas parceiras</span>
                <span>Entregadores</span>
            </div>
        </aside>
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
            <p>Acesse as categorias principais e avance para o pedido com mais rapidez.</p>
            <a class="btn" href="#categorias" data-track="home_customer_entry_click" data-track-component="profile_card">Fazer pedido</a>
        </article>

        <article class="home-profile-card surface-card">
            <small>Parceiro</small>
            <h3>Vender na Fox Delivery</h3>
            <p>Cadastre sua operacao e leve sua loja para uma jornada comercial mais objetiva.</p>
            <a class="btn ghost" href="./cadastro-parceiros.php" data-track="home_partner_entry_click" data-track-component="profile_card">Seja parceiro</a>
        </article>

        <article class="home-profile-card delivery surface-card">
            <small>Entregador</small>
            <h3>Entregar com a Fox</h3>
            <p>Entre no cadastro do entregador com um fluxo proprio para modalidade e documentacao.</p>
            <a class="btn ghost" href="./cadastro-entregador.php" data-track="home_delivery_entry_click" data-track-component="profile_card">Seja entregador</a>
        </article>
    </div>
</section>

<section class="container section home-section" id="categorias">
    <div class="section-head center home-centered-head">
        <span class="eyebrow">Categorias</span>
        <h2>Peca por categoria</h2>
        <p>Os principais tipos de pedido da Fox Delivery em acessos diretos e faceis de entender.</p>
    </div>

    <div class="home-category-grid home-category-grid-compact">
        <article class="home-category-card surface-card">
            <small>Refeicoes</small>
            <strong>Restaurante</strong>
            <p>Pratos, lanches e refeicoes do dia em um acesso rapido.</p>
        </article>

        <article class="home-category-card surface-card">
            <small>Compras</small>
            <strong>Mercado</strong>
            <p>Compras recorrentes com mais praticidade para a rotina.</p>
        </article>

        <article class="home-category-card surface-card">
            <small>Cuidados</small>
            <strong>Farmacia</strong>
            <p>Produtos essenciais em uma jornada objetiva e organizada.</p>
        </article>

        <article class="home-category-card surface-card">
            <small>Dia a dia</small>
            <strong>Conveniencia</strong>
            <p>Itens rapidos para diferentes momentos do dia.</p>
        </article>
    </div>
</section>

<section class="section section-soft home-process-section">
    <div class="container">
        <div class="section-head center home-centered-head">
            <span class="eyebrow">Como funciona</span>
            <h2>Uma jornada simples em 3 passos</h2>
            <p>Do primeiro clique ao acompanhamento da operacao, tudo fica mais claro para cada perfil.</p>
        </div>

        <div class="home-process-grid home-process-grid-compact">
            <article class="surface-card home-process-card">
                <span class="home-process-number">1</span>
                <strong>Escolha o que precisa</strong>
                <p>Veja as categorias ou entre direto na jornada certa.</p>
            </article>

            <article class="surface-card home-process-card">
                <span class="home-process-number">2</span>
                <strong>Inicie sua acao</strong>
                <p>Peca, cadastre sua loja ou siga para o cadastro de entregador.</p>
            </article>

            <article class="surface-card home-process-card">
                <span class="home-process-number">3</span>
                <strong>Acompanhe com clareza</strong>
                <p>Conte com orientacao, suporte e continuidade dentro da plataforma.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section home-section">
    <div class="section-head center home-centered-head">
        <span class="eyebrow">Confianca</span>
        <h2>Uma estrutura pensada para operar com clareza</h2>
        <p>A home da Fox Delivery organiza a entrada de clientes, parceiros e entregadores em uma experiencia mais objetiva.</p>
    </div>

    <div class="home-trust-grid">
        <article class="home-trust-card surface-card">
            <strong>Acessos separados</strong>
            <p>Cliente, parceiro e entregador entram na jornada certa desde o inicio.</p>
        </article>

        <article class="home-trust-card surface-card">
            <strong>Categorias claras</strong>
            <p>Restaurante, mercado, farmacia e conveniencia com leitura mais rapida.</p>
        </article>

        <article class="home-trust-card surface-card">
            <strong>Suporte e operacao</strong>
            <p>Ajuda, contato e cadastros conectados no mesmo ecossistema da plataforma.</p>
        </article>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Inicio';
$current = 'home';
require __DIR__ . '/includes/layout.php';
