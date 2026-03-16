<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

$businessName = get_business_setting('business_name', env('APP_NAME', 'Fox Delivery'));
$logo = get_business_setting('logo', '');
$phone = get_business_setting('phone', '(11) 9999-9999');
$email = get_business_setting('email_address', 'contato@foxdelivery.com.br');
$address = get_business_setting('address', 'São Paulo - SP');

$logoUrl = $logo ? sixammart_url('storage/app/public/business/' . ltrim((string) $logo, '/')) : '';

$pageTitle = $pageTitle ?? 'Fox Delivery';
$current = $current ?? 'home';
$content = $content ?? '';
$hidePageHeader = $hidePageHeader ?? false;
$hidePageFooter = $hidePageFooter ?? false;
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
<?php if (!$hidePageHeader): ?>
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
            <a class="<?= $current === 'home' ? 'active' : '' ?>" href="./index.php">Início</a>
            <a class="<?= $current === 'about' ? 'active' : '' ?>" href="./sobre.php">Sobre Nós</a>
            <a class="<?= $current === 'contact' ? 'active' : '' ?>" href="./contato.php">Contato</a>
            <a class="<?= $current === 'store' ? 'active' : '' ?>" href="./cadastro-loja.php">Cadastro de Lojas</a>
            <a class="<?= $current === 'delivery' ? 'active' : '' ?>" href="./cadastro-entregador.php">Cadastro Entregador</a>
        </nav>
        <div class="actions">
            <a class="btn ghost" href="<?= e(sixammart_url('login')) ?>">Entrar</a>
            <a class="btn" href="#apps">Baixar App</a>
        </div>
    </div>
</header>
<?php endif; ?>

<main><?= $content ?></main>

<?php if (!$hidePageFooter): ?>
<footer>
    <div class="container footer-grid">
        <div>
            <strong><?= e((string) $businessName) ?></strong>
            <p>Mercado, restaurantes, farmácia e conveniência em um só app.</p>
        </div>
        <div>
            <p>📞 <?= e((string) $phone) ?></p>
            <p>✉️ <?= e((string) $email) ?></p>
            <p>📍 <?= e((string) $address) ?></p>
        </div>
    </div>
</footer>
<?php endif; ?>
</body>
</html>
