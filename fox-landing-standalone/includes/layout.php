<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

$businessName = get_business_setting('business_name', env('APP_NAME', 'Fox Delivery'));
$phone = get_business_setting('phone', '(11) 9999-9999');
$email = get_business_setting('email_address', 'contato@foxdelivery.com.br');
$address = get_business_setting('address', 'São Paulo - SP');

$download = get_data_setting('admin_landing_page', 'download_user_app_links', []);
$appleStoreUrl = is_array($download) ? (string) ($download['apple_store_url'] ?? '') : '';
$playStoreUrl = is_array($download) ? (string) ($download['playstore_url'] ?? '') : '';
$appDownloadUrl = $playStoreUrl !== '' ? $playStoreUrl : ($appleStoreUrl !== '' ? $appleStoreUrl : '#');
$appDownloadAttrs = $appDownloadUrl !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '';
$partnerPanelLoginUrl = 'https://foxgodelivery.com.br/login/parceiro';

$brandImage = './Imagem/logo.png';

$pageTitle = $pageTitle ?? 'Fox Delivery';
$current = $current ?? 'home';
$content = $content ?? '';
$hidePageHeader = $hidePageHeader ?? false;
$hidePageFooter = $hidePageFooter ?? false;

$primaryNav = [
    ['id' => 'home', 'label' => 'Início', 'href' => './index.php'],
    ['id' => 'partners', 'label' => 'Para parceiros', 'href' => './cadastro-parceiros.php'],
    ['id' => 'help', 'label' => 'Ajuda', 'href' => './ajuda.php'],
];
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
        <a class="brand" href="./index.php" aria-label="<?= e((string) $businessName) ?>" data-track="nav_brand_click" data-track-component="header">
            <span class="brand-mark">
                <img src="<?= e($brandImage) ?>" alt="<?= e((string) $businessName) ?>">
            </span>
            <span class="brand-copy">
                <strong><?= e((string) $businessName) ?></strong>
                <small>Plataforma nacional de entregas</small>
            </span>
        </a>
        <nav class="primary-nav" aria-label="Principal">
            <?php foreach ($primaryNav as $item): ?>
                <a class="<?= $current === $item['id'] ? 'active' : '' ?>" href="<?= e($item['href']) ?>" data-track="nav_<?= e($item['id']) ?>_click" data-track-component="header">
                    <?= e($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="actions">
            <a class="btn ghost" href="<?= e($partnerPanelLoginUrl) ?>" target="_blank" rel="noopener noreferrer" data-track="panel_login_click" data-track-component="header">Entrar no painel</a>
            <a class="btn" href="<?= e($appDownloadUrl) ?>"<?= $appDownloadAttrs ?> data-track="app_download_header_click" data-track-component="header">Baixar app</a>
        </div>
    </div>
</header>
<?php endif; ?>

<main><?= $content ?></main>

<?php if (!$hidePageFooter): ?>
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand-block">
            <strong><?= e((string) $businessName) ?></strong>
            <p class="footer-note">Operação conectada para pedidos, lojas e entregadores, com experiência pensada para escala, agilidade e confiança.</p>
        </div>
        <div>
            <p class="footer-column-title">Plataforma</p>
            <div class="footer-nav">
                <a href="./index.php" data-track="footer_home_click" data-track-component="footer">Início</a>
                <a href="./ajuda.php" data-track="footer_help_click" data-track-component="footer">Ajuda</a>
            </div>
        </div>
        <div>
            <p class="footer-column-title">Jornadas</p>
            <div class="footer-nav">
                <a href="./cadastro-parceiros.php" data-track="footer_partners_click" data-track-component="footer">Quero vender na Fox Delivery</a>
                <a href="./cadastro-entregador.php" data-track="footer_delivery_click" data-track-component="footer">Quero entregar com a Fox Delivery</a>
                <a href="./index.php#apps" data-track="footer_apps_click" data-track-component="footer">Baixar aplicativo</a>
            </div>
        </div>
        <div>
            <p class="footer-column-title">Empresa</p>
            <div class="footer-nav">
                <a href="./sobre.php" data-track="footer_about_click" data-track-component="footer">Sobre a Fox Delivery</a>
                <a href="./contato.php" data-track="footer_contact_click" data-track-component="footer">Contato</a>
                <a href="<?= e($partnerPanelLoginUrl) ?>" target="_blank" rel="noopener noreferrer" data-track="footer_panel_login_click" data-track-component="footer">Entrar no painel</a>
            </div>
        </div>
        <div>
            <p class="footer-column-title">Atendimento</p>
            <p>Telefone: <?= e((string) $phone) ?></p>
            <p>E-mail: <?= e((string) $email) ?></p>
            <p>Base operacional: <?= e((string) $address) ?></p>
        </div>
    </div>
</footer>
<?php endif; ?>
<script>
    (function () {
        window.dataLayer = window.dataLayer || [];

        const rememberEvent = (payload) => {
            try {
                const storageKey = 'fox_delivery_event_log';
                const current = JSON.parse(sessionStorage.getItem(storageKey) || '[]');
                current.push(payload);
                sessionStorage.setItem(storageKey, JSON.stringify(current.slice(-80)));
            } catch (error) {
                return;
            }
        };

        const track = (eventName, detail = {}) => {
            const payload = {
                event: 'fox_delivery_event',
                event_name: eventName,
                page_path: window.location.pathname,
                timestamp: new Date().toISOString(),
                ...detail
            };

            window.dataLayer.push(payload);
            rememberEvent(payload);
            document.dispatchEvent(new CustomEvent('fox:track', { detail: payload }));
        };

        document.addEventListener('click', (event) => {
            const element = event.target.closest('[data-track]');
            if (!element) {
                return;
            }

            track(element.dataset.track, {
                component: element.dataset.trackComponent || '',
                href: element.getAttribute('href') || '',
                label: (element.dataset.trackLabel || element.textContent || '').trim()
            });
        });

        document.querySelectorAll('[data-track-form]').forEach((form) => {
            let started = false;

            const markStart = () => {
                if (started) {
                    return;
                }

                started = true;
                track(form.dataset.trackForm + '_start', {
                    component: 'form',
                    form_name: form.dataset.trackForm
                });
            };

            form.addEventListener('focusin', markStart);
            form.addEventListener('change', markStart);
            form.addEventListener('submit', () => {
                track(form.dataset.trackForm + '_submit', {
                    component: 'form',
                    form_name: form.dataset.trackForm
                });
            });
        });

        window.foxDeliveryTrack = track;
    })();
</script>
</body>
</html>
