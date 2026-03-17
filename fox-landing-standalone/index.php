<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$download = get_data_setting('admin_landing_page', 'download_user_app_links', []);
$apple = is_array($download) ? ($download['apple_store_url'] ?? '#') : '#';
$play = is_array($download) ? ($download['playstore_url'] ?? '#') : '#';

ob_start();
?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content">
            <span class="hero-kicker">Fox Delivery</span>
            <h1>Tudo o que voce precisa, entregue com a Fox Delivery</h1>
            <p>Peca refeicoes, mercado, farmacia e conveniencia com praticidade, seguranca e acompanhamento em tempo real.</p>
            <div class="cta-row">
                <a class="btn yellow" href="./contato.php">Pedir agora</a>
                <a class="btn" href="./cadastro-parceiros.php">Cadastrar operacao</a>
            </div>
            <div class="cta-row" id="apps">
                <a class="store" href="<?= e($apple) ?>">App Store</a>
                <a class="store" href="<?= e($play) ?>">Google Play</a>
            </div>
            <div class="hero-points">
                <span>Pedido rapido</span>
                <span>Entrega eficiente</span>
                <span>Experiencia Fox Delivery</span>
            </div>
        </div>
        <div class="hero-illustration">
            <div class="hero-visual">
                <img src="./Imagem/ChatGPT Image 10 de mar. de 2026, 18_51_32.png" alt="Entregador Fox Delivery com mochila, sacola de compras e produtos de mercado">
            </div>
        </div>
    </div>
</section>

<section class="container section center">
    <h2>Fox Delivery em um so lugar</h2>
    <p>Peca pelo celular ou computador com uma experiencia pensada para rotina urbana, conveniencia e agilidade.</p>
    <div class="cards five categories">
        <article>
            <small>Fox Delivery</small>
            <strong>Mercado</strong>
            <p>Compras do dia a dia com mais praticidade.</p>
        </article>
        <article>
            <small>Fox Delivery</small>
            <strong>Restaurantes</strong>
            <p>Refeicoes de diferentes estilos em um so app.</p>
        </article>
        <article>
            <small>Fox Delivery</small>
            <strong>Farmacia</strong>
            <p>Itens essenciais com mais agilidade na entrega.</p>
        </article>
        <article>
            <small>Fox Delivery</small>
            <strong>Entregas</strong>
            <p>Operacao logistica conectada a sua rotina.</p>
        </article>
        <article>
            <small>Fox Delivery</small>
            <strong>Conveniencia</strong>
            <p>Solucoes rapidas para diferentes momentos do dia.</p>
        </article>
    </div>
</section>

<section class="section section-soft">
    <div class="container center">
        <h2>Como funciona o Fox Delivery</h2>
        <div class="cards three how-it-works">
            <article>
                <strong>Explore</strong>
                <p>Encontre categorias, operacoes e ofertas dentro da plataforma Fox Delivery.</p>
            </article>
            <article>
                <strong>Finalize</strong>
                <p>Conclua seu pedido com uma jornada simples, rapida e segura.</p>
            </article>
            <article>
                <strong>Acompanhe</strong>
                <p>Visualize o andamento da entrega e receba com praticidade.</p>
            </article>
        </div>
        <div class="cta-center">
            <a class="btn" href="#apps">Baixar o app Fox Delivery</a>
        </div>
    </div>
</section>

<section class="container section split split-single">
    <a class="panel partner-panel" href="./cadastro-parceiros.php">
        <small>Fox Delivery Parceiros</small>
        <h3>Cadastre sua operacao na Fox Delivery</h3>
        <p>Leve sua loja ou sua operacao de entregas para uma jornada de cadastro dedicada, com visual profissional e integracao a plataforma Fox Delivery.</p>
        <span>Conhecer cadastro de parceiros</span>
    </a>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Inicio';
$current = 'home';
require __DIR__ . '/includes/layout.php';
