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
        <div>
            <h1>Tudo o que você precisa entregue na sua porta</h1>
            <p>Peça comida, mercado, farmácia e mais. Layout em pasta totalmente separada e sincronizada com o banco do 6amMart.</p>
            <div class="cta-row">
                <a class="btn yellow" href="./contato.php">Peça agora</a>
                <a class="btn" href="<?= e(sixammart_url('vendor/apply')) ?>">Cadastrar loja</a>
            </div>
            <div class="cta-row" id="apps">
                <a class="store" href="<?= e($apple) ?>">App Store</a>
                <a class="store" href="<?= e($play) ?>">Google Play</a>
            </div>
        </div>
        <div class="hero-card">
            <h3>Fox Delivery</h3>
            <p>Mercado · Restaurantes · Farmácia · Conveniência</p>
        </div>
    </div>
</section>

<section class="container section">
    <h2>Tudo em um só lugar</h2>
    <div class="cards five">
        <article>Mercado</article><article>Restaurantes</article><article>Farmácia</article><article>Entregas</article><article>Conveniência</article>
    </div>
</section>

<section class="container section">
    <h2>Como funciona o Fox Delivery</h2>
    <div class="cards three">
        <article><strong>1. Escolha</strong><p>Escolha o que precisa no aplicativo.</p></article>
        <article><strong>2. Peça</strong><p>Faça seu pedido em poucos cliques.</p></article>
        <article><strong>3. Receba</strong><p>Receba em minutos na sua casa.</p></article>
    </div>
</section>

<section class="container section split">
    <a class="panel" href="./cadastro-loja.php"><h3>Seja um Restaurante Parceiro</h3><p>Cadastro oficial do 6amMart com sincronização completa.</p></a>
    <a class="panel" href="./cadastro-entregador.php"><h3>Seja Entregador Parceiro</h3><p>Fluxo oficial de entregadores com banco compartilhado.</p></a>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Início';
$current = 'home';
require __DIR__ . '/includes/layout.php';
