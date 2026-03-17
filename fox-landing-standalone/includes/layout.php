<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

$businessName = get_business_setting('business_name', env('APP_NAME', 'Fox Delivery'));
$phone = get_business_setting('phone', '(11) 9999-9999');
$email = get_business_setting('email_address', 'contato@foxdelivery.com.br');
$address = get_business_setting('address', 'Sao Paulo - SP');

$brandImage = './Imagem/logo.png';

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
        <a class="brand" href="./index.php" aria-label="<?= e((string) $businessName) ?>">
            <span class="brand-mark">
                <img src="<?= e($brandImage) ?>" alt="<?= e((string) $businessName) ?>">
            </span>
            <span class="brand-copy">
                <strong><?= e((string) $businessName) ?></strong>
                <small>Plataforma oficial</small>
            </span>
        </a>
        <nav>
            <a class="<?= $current === 'home' ? 'active' : '' ?>" href="./index.php">Inicio</a>
            <a class="<?= $current === 'about' ? 'active' : '' ?>" href="./sobre.php">Sobre nos</a>
            <a class="<?= $current === 'contact' ? 'active' : '' ?>" href="./contato.php">Contato</a>
            <a class="<?= $current === 'partners' ? 'active' : '' ?>" href="./cadastro-parceiros.php">Parceiros</a>
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
            <p>Mercado, restaurantes, farmacia e conveniencia em uma experiencia pensada para a rotina urbana.</p>
        </div>
        <div>
            <p>Telefone: <?= e((string) $phone) ?></p>
            <p>E-mail: <?= e((string) $email) ?></p>
            <p>Localizacao: <?= e((string) $address) ?></p>
        </div>
    </div>
</footer>
<?php endif; ?>
</body>
</html>
