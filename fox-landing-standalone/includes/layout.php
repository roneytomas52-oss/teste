<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

$businessName = get_business_setting('business_name', env('APP_NAME', 'Fox Delivery'));
$logo = get_business_setting('logo', '');
$phone = get_business_setting('phone', '(11) 9999-9999');
$email = get_business_setting('email_address', 'contato@foxdelivery.com.br');
$address = get_business_setting('address', 'São Paulo - SP');
$download = get_data_setting('admin_landing_page', 'download_user_app_links', []);
$flutter = get_data_settings_by_type('flutter_landing_page');
$social = get_social_media();
$landingPageLinks = get_business_setting('landing_page_links', []);

$logoUrl = $logo ? storage_url('business', (string) $logo) : '';

$menu = [
    ['label' => 'Início', 'url' => './index.php', 'key' => 'home'],
    ['label' => 'Sobre Nós', 'url' => './sobre.php', 'key' => 'about'],
    ['label' => 'Contato', 'url' => './contato.php', 'key' => 'contact'],
    ['label' => 'Cadastro Loja', 'url' => './cadastro-loja.php', 'key' => 'store'],
    ['label' => 'Cadastro Entregador', 'url' => './cadastro-entregador.php', 'key' => 'delivery'],
];

$orderUrl = is_array($landingPageLinks) && !empty($landingPageLinks['web_app_url'])
    ? (string) $landingPageLinks['web_app_url']
    : sixammart_url('/');

$storeRegisterUrl = !empty($flutter['join_seller_button_url'])
    ? (string) $flutter['join_seller_button_url']
    : sixammart_url('vendor/apply');

$deliveryRegisterUrl = !empty($flutter['join_delivery_man_button_url'])
    ? (string) $flutter['join_delivery_man_button_url']
    : sixammart_url('deliveryman/apply');

$pageTitle = $pageTitle ?? 'Fox Delivery';
$current = $current ?? 'home';
$content = $content ?? '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e((string) $pageTitle) ?></title>
    <link rel="stylesheet" href="./assets/style.css">
</head>
<body>
<header class="header">
    <div class="container nav">
        <a class="brand" href="./index.php">
            <?php if ($logoUrl): ?>
                <img src="<?= e($logoUrl) ?>" alt="<?= e((string) $businessName) ?>">
            <?php else: ?>
                <strong><?= e((string) $businessName) ?></strong>
            <?php endif; ?>
        </a>
        <nav>
            <?php foreach ($menu as $item): ?>
                <a class="<?= $current === $item['key'] ? 'active' : '' ?>" href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="actions">
            <a class="btn ghost" href="<?= e(sixammart_url('login/admin')) ?>">Entrar</a>
            <a class="btn" href="#apps">Baixar App</a>
        </div>
    </div>
</header>

<main><?= $content ?></main>

<footer>
    <div class="container footer-grid">
        <div>
            <strong><?= e((string) $businessName) ?></strong>
            <p>Landing standalone sincronizada com 6amMart (admin + banco)</p>
            <?php if ($social): ?>
                <div class="social">
                    <?php foreach ($social as $sm): ?>
                        <a href="<?= e((string) ($sm['link'] ?? '#')) ?>" target="_blank"><?= e((string) ($sm['name'] ?? 'Rede')) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <p>📞 <?= e((string) $phone) ?></p>
            <p>✉️ <?= e((string) $email) ?></p>
            <p>📍 <?= e((string) $address) ?></p>
            <p><a class="order-link" href="<?= e($orderUrl) ?>">Peça agora no app/web</a></p>
        </div>
    </div>
</footer>
</body>
</html>
