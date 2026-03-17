<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$vendorApplyUrl = sixammart_url('vendor/apply');

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Loja</h1>
        <p>Fluxo completo com os campos oficiais do 6amMart, incluindo validações e captcha nativo.</p>
    </div>
</section>

<section class="container section contact registration-layout">
    <div class="panel">
        <h3>Requisitos obrigatórios (Brasil)</h3>
        <ul class="requirements">
            <li>CNPJ/CPF válido do responsável pelo estabelecimento.</li>
            <li>Telefone, e-mail e endereço comercial atualizados.</li>
            <li>Documento com foto do responsável legal.</li>
            <li>Informações completas de operação e atendimento.</li>
            <li>Validação completa com captcha oficial.</li>
        </ul>

        <div class="steps-inline">
            <span class="step active">1. Dados da loja</span>
            <span class="step">2. Dados do responsável</span>
            <span class="step">3. Conclusão</span>
        </div>

        <p>O cadastro oficial está incorporado nesta página, mantendo o padrão profissional sem redirecionamento externo.</p>
    </div>

    <div class="panel embedded-panel">
        <div class="frame-zoom">
            <iframe
                class="official-frame"
                src="<?= e($vendorApplyUrl) ?>"
                title="Cadastro oficial de loja"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<div id="registration-complete-message" class="container section" style="display:none; text-align:center;">
    <div class="panel" style="max-width: 980px; margin: 0 auto;">
        <h2 style="margin-bottom: 14px;">Cadastro finalizado</h2>
        <p style="font-size: 18px; line-height:1.6;">
            Obrigado! Logo um agente entrará em contato pelo número de telefone e e-mail cadastrado.
        </p>
    </div>
</div>

<footer class="simple-footer">
    <div class="container">
        <p>© <?= date('Y') ?> Fox Delivery. Todos os direitos reservados.</p>
    </div>
</footer>

<script>
    (function () {
        const frame = document.querySelector('.official-frame');
        const completeMessage = document.getElementById('registration-complete-message');
        const registrationSection = document.querySelector('.registration-layout');

        if (!frame || !completeMessage || !registrationSection) {
            return;
        }

        const showCompleteMessage = () => {
            registrationSection.style.display = 'none';
            completeMessage.style.display = 'block';
        };

        const checkCompletion = () => {
            try {
                const currentUrl = frame.contentWindow.location.href;
                if (currentUrl.includes('/vendor/final-step')) {
                    showCompleteMessage();
                }
            } catch (error) {
                // Ignore cross-origin access errors and keep polling.
            }
        };

        frame.addEventListener('load', checkCompletion);
        setInterval(checkCompletion, 1200);
    })();
</script>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Loja';
$current = 'store';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
