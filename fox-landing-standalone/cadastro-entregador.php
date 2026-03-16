<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/official_embed.php';

$deliveryApplyUrl = sixammart_url('deliveryman/apply');
[$embeddedForm, $embedError] = build_embedded_official_form($deliveryApplyUrl);

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Entregador</h1>
        <p>Mesmo cadastro oficial do 6amMart dentro desta página, mantendo mapa, etapas, captcha e validações nativas.</p>
    </div>
</section>

<section class="container section contact registration-layout">
    <div class="panel">
        <h3>Requisitos obrigatórios (Brasil)</h3>
        <ul class="requirements">
            <li>Documento com foto e CPF válidos.</li>
            <li>Telefone, e-mail e endereço atualizados.</li>
            <li>Área de cobertura com marcação no mapa.</li>
            <li>Dados de modalidade e documentos do veículo.</li>
            <li>Validação completa com captcha oficial.</li>
        </ul>

        <div class="steps-inline">
            <span class="step active">1. Dados pessoais</span>
            <span class="step">2. Dados de trabalho</span>
            <span class="step">3. Conclusão</span>
        </div>

        <p>Fluxo oficial incorporado na própria página para finalizar o cadastro sem redirecionamento externo.</p>

        <p>
            <a class="btn ghost" href="<?= e($deliveryApplyUrl) ?>" target="_blank" rel="noopener noreferrer">
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
$pageTitle = 'Fox Delivery - Cadastro de Entregador';
$current = 'delivery';
require __DIR__ . '/includes/layout.php';
