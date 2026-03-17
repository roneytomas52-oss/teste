<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$vendorApplyUrl = sixammart_url('vendor/apply');
$deliveryApplyUrl = sixammart_url('deliveryman/apply');

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro Oficial 6amMart</h1>
        <p>Formulário único para loja e entregador com os mesmos campos e validações oficiais, 100% sincronizado com o painel administrativo.</p>
    </div>
</section>

<section class="container section contact registration-layout unified-registration">
    <div class="panel">
        <h3>Tipo de cadastro</h3>
        <div class="switcher" role="tablist" aria-label="Tipo de cadastro">
            <button class="switch-btn active" data-target="store" role="tab" aria-selected="true">Loja</button>
            <button class="switch-btn" data-target="delivery" role="tab" aria-selected="false">Entregador</button>
        </div>

        <ul class="requirements" id="requirements-store">
            <li>Dados da loja e responsável legal.</li>
            <li>Mesmos campos oficiais do fluxo <code>/vendor/apply</code>.</li>
            <li>Processo de aprovação e status no painel admin 6amMart.</li>
        </ul>

        <ul class="requirements" id="requirements-delivery" style="display:none;">
            <li>Dados pessoais, identidade e área de cobertura.</li>
            <li>Mesmos campos oficiais do fluxo <code>/deliveryman/apply</code>.</li>
            <li>Processo de aprovação e status no painel admin 6amMart.</li>
        </ul>
    </div>

    <div class="panel embedded-panel">
        <div class="frame-zoom">
            <iframe class="official-frame" id="frame-store" src="<?= e($vendorApplyUrl) ?>" title="Cadastro oficial de loja" loading="lazy"></iframe>
            <iframe class="official-frame" id="frame-delivery" src="<?= e($deliveryApplyUrl) ?>" title="Cadastro oficial de entregador" loading="lazy" style="display:none;"></iframe>
        </div>
    </div>
</section>

<div id="registration-complete-message" class="container section" style="display:none; text-align:center;">
    <div class="panel" style="max-width: 980px; margin: 0 auto;">
        <h2 style="margin-bottom: 14px;">Cadastro finalizado</h2>
        <p style="font-size: 18px; line-height:1.6;">Obrigado! Seu cadastro foi enviado no fluxo oficial e está sincronizado com o painel de administração.</p>
    </div>
</div>

<footer class="simple-footer">
    <div class="container">
        <p>© <?= date('Y') ?> Fox Delivery. Todos os direitos reservados.</p>
    </div>
</footer>

<script>
    (function () {
        const buttons = document.querySelectorAll('.switch-btn');
        const frameStore = document.getElementById('frame-store');
        const frameDelivery = document.getElementById('frame-delivery');
        const reqStore = document.getElementById('requirements-store');
        const reqDelivery = document.getElementById('requirements-delivery');
        const completeMessage = document.getElementById('registration-complete-message');
        const registrationSection = document.querySelector('.registration-layout');

        const setActive = (type) => {
            const storeActive = type === 'store';
            frameStore.style.display = storeActive ? 'block' : 'none';
            frameDelivery.style.display = storeActive ? 'none' : 'block';
            reqStore.style.display = storeActive ? 'block' : 'none';
            reqDelivery.style.display = storeActive ? 'none' : 'block';

            buttons.forEach((button) => {
                const active = button.dataset.target === type;
                button.classList.toggle('active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => setActive(button.dataset.target));
        });

        const showCompleteMessage = () => {
            if (!completeMessage || !registrationSection) {
                return;
            }
            registrationSection.style.display = 'none';
            completeMessage.style.display = 'block';
        };

        const checkStoreCompletion = () => {
            try {
                const currentUrl = frameStore.contentWindow.location.href;
                if (currentUrl.includes('/vendor/final-step')) {
                    showCompleteMessage();
                }
            } catch (error) {
                // Ignore cross-origin access errors and keep polling.
            }
        };

        frameStore.addEventListener('load', checkStoreCompletion);
        setInterval(checkStoreCompletion, 1200);

        setActive('store');
    })();
</script>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro Parceiros';
$current = 'partners';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
