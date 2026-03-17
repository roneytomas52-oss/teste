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
            <h1>Tudo que você precisa entregue na sua porta</h1>
            <p>Peça comida, mercado, farmácia e conveniência sem sair de casa.</p>
            <div class="cta-row">
                <a class="btn yellow" href="./contato.php">Peça agora</a>
                <a class="btn" href="./cadastro-parceiros.php">Cadastrar loja/entregador</a>
            </div>
            <div class="cta-row" id="apps">
                <a class="store" href="<?= e($apple) ?>">App Store</a>
                <a class="store" href="<?= e($play) ?>">Google Play</a>
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
    <h2>Tudo em um só lugar</h2>
    <p>Faça pedidos no celular ou computador com entrega rápida e segura.</p>
    <div class="cards five categories">
        <article><span>🛒</span><strong>Mercado</strong></article>
        <article><span>🍔</span><strong>Restaurantes</strong></article>
        <article><span>💊</span><strong>Farmácia</strong></article>
        <article><span>🛵</span><strong>Entregas</strong></article>
        <article><span>🏪</span><strong>Conveniência</strong></article>
    </div>
</section>

<section class="section section-soft">
    <div class="container center">
        <h2>Como funciona o Fox Delivery</h2>
        <div class="cards three">
            <article><strong>Escolha</strong><p>Selecione produtos e lojas pelo app.</p></article>
            <article><strong>Peça</strong><p>Finalize em poucos cliques com segurança.</p></article>
            <article><strong>Receba</strong><p>Acompanhe e receba em minutos.</p></article>
        </div>
        <div class="cta-center">
            <a class="btn" href="#apps">Baixe o aplicativo grátis!</a>
        </div>
    </div>
</section>

<section class="container section split">
    <a class="panel" href="./cadastro-parceiros.php">
        <h3>Cadastro Loja e Entregador</h3>
        <p>Usa os formulários oficiais do 6amMart com os mesmos campos e sincronização total no admin.</p>
    </a>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Início';
$current = 'home';
require __DIR__ . '/includes/layout.php';
