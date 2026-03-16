<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$deliveryApplyUrl = sixammart_url('deliveryman/apply');

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Entregador</h1>
        <p>Fluxo completo com os mesmos campos oficiais do 6amMart, incluindo mapa de atuação e captcha nativo.</p>
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

        <p>O cadastro oficial está incorporado nesta página, mantendo o padrão profissional sem redirecionamento externo.</p>

    </div>

    <div class="panel embedded-panel">
        <iframe
            class="official-frame"
            src="<?= e($deliveryApplyUrl) ?>"
            title="Cadastro oficial de entregador"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</section>

<footer class="simple-footer">
    <div class="container">
        <p>© <?= date('Y') ?> Fox Delivery. Todos os direitos reservados.</p>
    </div>
</footer>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Entregador';
$current = 'delivery';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
