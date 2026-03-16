<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$deliveryApplyUrl = sixammart_url('deliveryman/apply');

ob_start();
?>
<section class="container section">
    <div class="panel embedded-panel" style="max-width: 980px; margin: 0 auto;">
        <iframe
            class="official-frame"
            src="<?= e($deliveryApplyUrl) ?>"
            title="Cadastro oficial de entregador"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

    <div id="registration-complete-message" class="panel" style="max-width: 980px; margin: 0 auto; display:none; text-align:center;">
        <h2 style="margin-bottom: 14px;">Cadastro finalizado</h2>
        <p style="font-size: 18px; line-height:1.6;">
            Obrigado! Logo um agente entrará em contato pelo número de telefone e e-mail cadastrado.
        </p>
    </div>
</section>

<script>
    (function () {
        const frame = document.querySelector('.official-frame');
        const completeMessage = document.getElementById('registration-complete-message');

        if (!frame || !completeMessage) {
            return;
        }

        const showCompleteMessage = () => {
            frame.closest('.embedded-panel').style.display = 'none';
            completeMessage.style.display = 'block';
        };

        const checkCompletion = () => {
            try {
                const currentUrl = frame.contentWindow.location.href;
                if (currentUrl.includes('/deliveryman/final-step')) {
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
$pageTitle = 'Fox Delivery - Cadastro de Entregador';
$current = 'delivery';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
