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
                <a class="btn" href="./cadastro-loja.php">Cadastrar loja</a>
            </div>
            <div class="cta-row" id="apps">
                <a class="store" href="<?= e($apple) ?>">App Store</a>
                <a class="store" href="<?= e($play) ?>">Google Play</a>
            </div>
        </div>
        <div class="hero-illustration">
            <img src="./assets/fox-mascot.svg" alt="Mascote Fox Delivery com sacola na moto">
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
    <a class="panel" href="./cadastro-loja.php">
        <h3>Cadastro de Lojas</h3>
        <p>Fluxo em padrão brasileiro e integração ao painel 6amMart.</p>
    </a>
    <a class="panel" href="./cadastro-entregador.php">
        <h3>Cadastro Entregador</h3>
        <p>Validação de RG/CPF/CNH e finalização oficial no 6amMart.</p>
    </a>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Início';
$current = 'home';
require __DIR__ . '/includes/layout.php';
