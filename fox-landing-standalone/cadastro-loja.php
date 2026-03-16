<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/official_embed.php';

$vendorApplyUrl = sixammart_url('vendor/apply');
[$embeddedForm, $embedError] = build_embedded_official_form($vendorApplyUrl);

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Lojas</h1>
        <p>Mesmo cadastro oficial do 6amMart dentro desta página, mantendo mapa, etapas, captcha e validações nativas.</p>
    </div>
</section>

<section class="container section contact registration-layout">
    <div class="panel">
        <h3>Requisitos obrigatórios (Brasil)</h3>
        <ul class="requirements">
            <li>CNPJ ativo e válido.</li>
            <li>Responsável legal e dados de contato.</li>
            <li>Endereço comercial com geolocalização no mapa.</li>
            <li>Inscrição estadual (quando aplicável).</li>
            <li>Documentos, dados bancários e captcha obrigatório.</li>
        </ul>

        <div class="steps-inline">
            <span class="step active">1. Informações gerais</span>
            <span class="step">2. Plano de negócio</span>
            <span class="step">3. Conclusão</span>
        </div>

        <p>Fluxo oficial incorporado na própria página para finalizar o cadastro sem sair do layout Fox Delivery.</p>

        <p>
            <a class="btn ghost" href="<?= e($vendorApplyUrl) ?>" target="_blank" rel="noopener noreferrer">
                Abrir em nova aba (fallback)
            </a>
        </p>
    </div>

    <div class="panel embedded-panel">
        <?php if ($embedError !== null): ?>
            <div class="alert error"><?= e($embedError) ?></div>
        <?php else: ?>
            <div class="official-embed"><?= $embeddedForm ?></div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Loja';
$current = 'store';
require __DIR__ . '/includes/layout.php';
