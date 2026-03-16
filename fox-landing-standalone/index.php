<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$download = get_data_setting('admin_landing_page', 'download_user_app_links', []);
$landingSettings = get_data_settings_by_type('admin_landing_page');
$flutterSettings = get_data_settings_by_type('flutter_landing_page');
$landingPageLinks = get_business_setting('landing_page_links', []);

$apple = is_array($download) ? ($download['apple_store_url'] ?? '#') : '#';
$play = is_array($download) ? ($download['playstore_url'] ?? '#') : '#';

$orderUrl = is_array($landingPageLinks) && !empty($landingPageLinks['web_app_url'])
    ? (string) $landingPageLinks['web_app_url']
    : sixammart_url('/');

$storeRegisterUrl = !empty($flutterSettings['join_seller_button_url'])
    ? (string) $flutterSettings['join_seller_button_url']
    : sixammart_url('vendor/apply');

$deliveryRegisterUrl = !empty($flutterSettings['join_delivery_man_button_url'])
    ? (string) $flutterSettings['join_delivery_man_button_url']
    : sixammart_url('deliveryman/apply');

$modules = get_active_modules(5);
$features = get_active_admin_features(3);

$heroTitle = (string)($landingSettings['fixed_header_title'] ?? 'Tudo o que você precisa entregue na sua porta');
$heroSub = (string)($landingSettings['fixed_header_sub_title'] ?? 'Peça comida, mercado, farmácia e mais.');
$moduleTitle = (string)($landingSettings['fixed_module_title'] ?? 'Tudo em um só lugar');
$moduleSub = (string)($landingSettings['fixed_module_sub_title'] ?? 'Peça tudo pelo celular ou computador com entrega rápida e segura.');

ob_start();
?>
<section class="hero">
    <div class="container hero-grid">
        <div>
            <h1><?= e($heroTitle) ?></h1>
            <p><?= e($heroSub) ?></p>
            <div class="cta-row">
                <a class="btn yellow" href="<?= e($orderUrl) ?>">Peça agora</a>
                <a class="btn" href="<?= e($storeRegisterUrl) ?>">Cadastrar loja</a>
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
    <h2><?= e($moduleTitle) ?></h2>
    <p class="subtitle"><?= e($moduleSub) ?></p>
    <div class="cards five with-image">
        <?php if ($modules): ?>
            <?php foreach ($modules as $module): ?>
                <article>
                    <img src="<?= e((string)($module['icon_full_url'] ?? '')) ?>" alt="<?= e((string)($module['module_name'] ?? 'Módulo')) ?>">
                    <h4><?= e((string)($module['module_name'] ?? 'Módulo')) ?></h4>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <article><h4>Mercado</h4></article>
            <article><h4>Restaurantes</h4></article>
            <article><h4>Farmácia</h4></article>
            <article><h4>Entregas</h4></article>
            <article><h4>Conveniência</h4></article>
        <?php endif; ?>
    </div>
</section>

<section class="container section">
    <h2>Como funciona o Fox Delivery</h2>
    <div class="cards three with-image">
        <?php if ($features): ?>
            <?php foreach ($features as $idx => $feature): ?>
                <article>
                    <img src="<?= e((string)($feature['image_full_url'] ?? '')) ?>" alt="<?= e((string)($feature['title'] ?? 'Passo')) ?>">
                    <h4><?= e((string)($feature['title'] ?? ('Passo ' . ($idx + 1)))) ?></h4>
                    <p><?= e((string)($feature['sub_title'] ?? '')) ?></p>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <article><h4>1. Escolha</h4><p>Escolha o que precisa no aplicativo.</p></article>
            <article><h4>2. Peça</h4><p>Faça seu pedido em poucos cliques.</p></article>
            <article><h4>3. Receba</h4><p>Receba em minutos na sua casa.</p></article>
        <?php endif; ?>
    </div>
</section>

<section class="container section split">
    <a class="panel" href="<?= e($storeRegisterUrl) ?>">
        <h3><?= e((string)($flutterSettings['join_seller_title'] ?? 'Seja um Restaurante Parceiro')) ?></h3>
        <p><?= e((string)($flutterSettings['join_seller_sub_title'] ?? 'Cadastro oficial do 6amMart com sincronização completa.')) ?></p>
    </a>
    <a class="panel" href="<?= e($deliveryRegisterUrl) ?>">
        <h3><?= e((string)($flutterSettings['join_delivery_man_title'] ?? 'Seja Entregador Parceiro')) ?></h3>
        <p><?= e((string)($flutterSettings['join_delivery_man_sub_title'] ?? 'Fluxo oficial de entregadores com banco compartilhado.')) ?></p>
    </a>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Início';
$current = 'home';
require __DIR__ . '/includes/layout.php';
