<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/registration_portal.php';

$copyByType = registration_copy_map();
$syncStatus = registration_sync_status();

ob_start();
?>
<section class="hero registration-hero partner-hero">
    <div class="container partner-hero-shell">
        <div class="partner-hero-copy">
            <span class="partner-hero-kicker">Credenciamento oficial Fox Delivery</span>
            <h1>Escolha a jornada certa para o seu cadastro</h1>
            <p>Loja e entregador seguem agora em fluxos separados, com formul&aacute;rios pr&oacute;prios, visual profissional e integra&ccedil;&atilde;o direta com o painel administrativo da Fox Delivery.</p>
            <div class="partner-hero-points">
                <span>Fluxos independentes</span>
                <span>Mesmos requisitos do painel</span>
                <span>Sincroniza&ccedil;&atilde;o com o banco principal</span>
            </div>
        </div>

        <div class="partner-hero-card">
            <span class="partner-hero-card-label">Como funciona</span>
            <div class="partner-hero-card-item">
                <strong>1. Selecione o perfil</strong>
                <p>Escolha se o cadastro ser&aacute; de loja parceira ou de entregador.</p>
            </div>
            <div class="partner-hero-card-item">
                <strong>2. Preencha a tela dedicada</strong>
                <p>Cada fluxo possui campos espec&iacute;ficos, conforme as exig&ecirc;ncias oficiais da opera&ccedil;&atilde;o.</p>
            </div>
            <div class="partner-hero-card-item">
                <strong>3. Envio para o painel</strong>
                <p>As informa&ccedil;&otilde;es seguem para o ambiente administrativo da Fox Delivery, sem retrabalho manual.</p>
            </div>
        </div>
    </div>
</section>

<section class="container section registration-selector partner-selection">
    <div class="registration-selector-head partner-selection-head">
        <span class="panel-kicker">Jornada separada</span>
        <h2>Selecione o tipo de cadastro</h2>
        <p>Defina abaixo qual parceiro ser&aacute; credenciado. Cada op&ccedil;&atilde;o abre uma tela exclusiva, com valida&ccedil;&atilde;o alinhada ao painel e experi&ecirc;ncia mais organizada para o usu&aacute;rio final.</p>
    </div>

    <?php if (!$syncStatus['is_ready']): ?>
        <?= registration_render_alerts([], $syncStatus['issues']) ?>
    <?php endif; ?>

    <div class="partner-selector-grid">
        <article class="partner-selector-card store-card">
            <div class="partner-card-top">
                <span class="selector-badge">Loja</span>
                <span class="selector-icon" aria-hidden="true">LOJ</span>
            </div>
            <h3>Cadastro de loja parceira</h3>
            <p>Fluxo comercial dedicado ao lojista, ao respons&aacute;vel legal, aos dados fiscais da opera&ccedil;&atilde;o e &agrave; configura&ccedil;&atilde;o do neg&oacute;cio.</p>
            <ul class="requirements partner-card-list">
                <?php foreach ($copyByType['store']['requirements'] as $item): ?>
                    <li><?= $item ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="partner-card-meta">
                <span>Respons&aacute;vel legal</span>
                <span>Dados da loja</span>
                <span>Plano comercial</span>
            </div>
            <div class="selector-footer">
                <a class="btn partner-cta" href="./cadastro-loja.php">Abrir cadastro de loja</a>
            </div>
        </article>

        <article class="partner-selector-card delivery-card">
            <div class="partner-card-top">
                <span class="selector-badge delivery">Entregador</span>
                <span class="selector-icon delivery" aria-hidden="true">ENT</span>
            </div>
            <h3>Cadastro de entregador</h3>
            <p>Fluxo operacional dedicado ao credenciamento do entregador, documenta&ccedil;&atilde;o pessoal, zona de atendimento, ve&iacute;culo e dados de trabalho.</p>
            <ul class="requirements partner-card-list">
                <?php foreach ($copyByType['delivery']['requirements'] as $item): ?>
                    <li><?= $item ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="partner-card-meta">
                <span>Documento pessoal</span>
                <span>Zona e ve&iacute;culo</span>
                <span>Credenciamento operacional</span>
            </div>
            <div class="selector-footer">
                <a class="btn partner-cta delivery" href="./cadastro-entregador.php">Abrir cadastro de entregador</a>
            </div>
        </article>
    </div>

    <div class="panel sync-overview-card">
        <div class="sync-overview-copy">
            <span class="panel-kicker">Sincroniza&ccedil;&atilde;o oficial</span>
            <h3>Integra&ccedil;&atilde;o com painel e banco principal</h3>
            <p>Os formul&aacute;rios da Fox Delivery utilizam as mesmas op&ccedil;&otilde;es oficiais do banco principal e encaminham o envio para os endpoints administrativos, reduzindo risco de perda de cadastro e inconsist&ecirc;ncia operacional.</p>
        </div>
        <div class="sync-overview-badges">
            <span class="<?= $syncStatus['db_ready'] ? 'ok' : 'warn' ?>">Banco principal <?= $syncStatus['db_ready'] ? 'conectado' : 'revisar' ?></span>
            <span class="<?= $syncStatus['api_ready'] ? 'ok' : 'warn' ?>">API oficial <?= $syncStatus['api_ready'] ? 'configurada' : 'revisar' ?></span>
            <span class="<?= $syncStatus['is_ready'] ? 'ok' : 'warn' ?>">Sincroniza&ccedil;&atilde;o <?= $syncStatus['is_ready'] ? 'pronta' : 'parcial' ?></span>
        </div>
    </div>
</section>
<?php

$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Parceiros';
$current = 'partners';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
