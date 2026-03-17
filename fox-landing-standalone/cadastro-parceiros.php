<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/registration_portal.php';

$copyByType = registration_copy_map();
$syncStatus = registration_sync_status();

ob_start();
?>
<section class="hero registration-hero">
    <div class="container registration-hero-content">
        <h1>Cadastro de Parceiros Fox Delivery</h1>
        <p>Escolha abaixo o fluxo correto para iniciar seu credenciamento. Loja e entregador agora seguem em p&aacute;ginas separadas, com formul&aacute;rios independentes e integra&ccedil;&atilde;o direta ao painel administrativo.</p>
    </div>
</section>

<section class="container section registration-selector">
    <div class="registration-selector-head">
        <span class="panel-kicker">Jornada separada</span>
        <h2>Selecione o tipo de cadastro</h2>
        <p>Cada parceiro segue para uma tela pr&oacute;pria, com campos espec&iacute;ficos, valida&ccedil;&otilde;es alinhadas ao painel e envio para o fluxo oficial da Fox Delivery.</p>
    </div>

    <?php if (!$syncStatus['is_ready']): ?>
        <?= registration_render_alerts([], $syncStatus['issues']) ?>
    <?php endif; ?>

    <div class="partner-selector-grid">
        <article class="partner-selector-card">
            <span class="selector-badge">Loja</span>
            <h3>Cadastro de loja parceira</h3>
            <p>Fluxo dedicado ao cadastro comercial da loja, do respons&aacute;vel legal, dos documentos fiscais e do modelo operacional.</p>
            <ul class="requirements">
                <?php foreach ($copyByType['store']['requirements'] as $item): ?>
                    <li><?= $item ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="selector-footer">
                <a class="btn" href="./cadastro-loja.php">Ir para cadastro de loja</a>
            </div>
        </article>

        <article class="partner-selector-card">
            <span class="selector-badge delivery">Entregador</span>
            <h3>Cadastro de entregador</h3>
            <p>Fluxo dedicado ao credenciamento do entregador, documenta&ccedil;&atilde;o pessoal, zona de atua&ccedil;&atilde;o, ve&iacute;culo e dados operacionais.</p>
            <ul class="requirements">
                <?php foreach ($copyByType['delivery']['requirements'] as $item): ?>
                    <li><?= $item ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="selector-footer">
                <a class="btn" href="./cadastro-entregador.php">Ir para cadastro de entregador</a>
            </div>
        </article>
    </div>

    <div class="panel sync-overview-card">
        <div class="sync-overview-copy">
            <span class="panel-kicker">Sincroniza&ccedil;&atilde;o</span>
            <h3>V&iacute;nculo com painel e banco principal</h3>
            <p>Os formul&aacute;rios da Fox Delivery usam as mesmas op&ccedil;&otilde;es oficiais do banco principal e encaminham o envio para os endpoints oficiais do painel administrativo.</p>
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
