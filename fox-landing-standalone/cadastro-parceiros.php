<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$activeType = ($_GET['tipo'] ?? 'store') === 'delivery' ? 'delivery' : 'store';
$vendorApplyUrl = sixammart_url('vendor/apply');
$deliveryApplyUrl = sixammart_url('deliveryman/apply');

ob_start();
?>
<section class="hero registration-hero">
    <div class="container registration-hero-content">
        <h1>Cadastro Oficial 6amMart</h1>
        <p>Formulario unico para loja e entregador com visual inspirado na referencia e sincronizacao direta com o painel administrativo.</p>
    </div>
</section>

<section class="container section contact registration-layout unified-registration">
    <aside class="panel registration-side">
        <span class="panel-kicker">Cadastro oficial</span>
        <h2>Tipo de cadastro</h2>

        <div class="switcher" role="tablist" aria-label="Tipo de cadastro">
            <button class="switch-btn <?= $activeType === 'store' ? 'active' : '' ?>" data-target="store" role="tab" aria-selected="<?= $activeType === 'store' ? 'true' : 'false' ?>">Loja</button>
            <button class="switch-btn <?= $activeType === 'delivery' ? 'active' : '' ?>" data-target="delivery" role="tab" aria-selected="<?= $activeType === 'delivery' ? 'true' : 'false' ?>">Entregador</button>
        </div>

        <ul class="requirements" id="requirements-store" <?= $activeType === 'store' ? '' : 'style="display:none;"' ?>>
            <li>Dados da loja e do responsavel legal.</li>
            <li>Mesmos campos oficiais do fluxo <code>/vendor/apply</code>.</li>
            <li>Status e aprovacao refletidos no mesmo banco do admin.</li>
        </ul>

        <ul class="requirements" id="requirements-delivery" <?= $activeType === 'delivery' ? '' : 'style="display:none;"' ?>>
            <li>Dados pessoais, identidade e area de cobertura.</li>
            <li>Mesmos campos oficiais do fluxo <code>/deliveryman/apply</code>.</li>
            <li>Status e aprovacao refletidos no mesmo banco do admin.</li>
        </ul>

        <div class="sync-note">
            <strong>Sincronizacao real</strong>
            <p>Os cadastros continuam sendo enviados para os endpoints oficiais do 6amMart, sem duplicar regras locais.</p>
        </div>
    </aside>

    <div class="panel embedded-panel registration-frame-shell">
        <div class="frame-title">
            <span>fornecedor</span>
            <strong>aplicativo</strong>
        </div>

        <div class="frame-steps" aria-hidden="true">
            <span class="frame-step active">Informacoes Gerais</span>
            <span class="frame-step">Plano de negocios</span>
            <span class="frame-step">Completo</span>
        </div>

        <div class="frame-window">
            <iframe class="official-frame" id="frame-store" src="<?= e($vendorApplyUrl) ?>" title="Cadastro oficial de loja" loading="lazy" <?= $activeType === 'store' ? '' : 'style="display:none;"' ?>></iframe>
            <iframe class="official-frame" id="frame-delivery" src="<?= e($deliveryApplyUrl) ?>" title="Cadastro oficial de entregador" loading="lazy" <?= $activeType === 'delivery' ? '' : 'style="display:none;"' ?>></iframe>
        </div>
    </div>
</section>

<div id="registration-complete-message" class="container section registration-complete" style="display:none;">
    <div class="panel">
        <h2>Cadastro finalizado</h2>
        <p>Seu cadastro foi enviado no fluxo oficial e ja esta sincronizado com o painel administrativo.</p>
    </div>
</div>

<script>
    (function () {
        const buttons = document.querySelectorAll('.switch-btn');
        const frameStore = document.getElementById('frame-store');
        const frameDelivery = document.getElementById('frame-delivery');
        const reqStore = document.getElementById('requirements-store');
        const reqDelivery = document.getElementById('requirements-delivery');
        const completeMessage = document.getElementById('registration-complete-message');
        const registrationSection = document.querySelector('.registration-layout');

        const cleanupFrame = (frame) => {
            try {
                const doc = frame.contentDocument || frame.contentWindow.document;
                if (!doc || !doc.body) {
                    return;
                }

                if (!doc.getElementById('fox-embedded-cleanup')) {
                    const style = doc.createElement('style');
                    style.id = 'fox-embedded-cleanup';
                    style.textContent = `
                        header, footer, .header, .footer, .navbar, .nav-bar, .topbar, .top-bar, .menubar,
                        .menu-bar, .copyright, .copyright-area, .footer-area, .landing-footer, .web-footer,
                        [class*="footer"], [class*="Footer"], [id*="footer"], [id*="Footer"] {
                            display: none !important;
                        }

                        body {
                            padding-top: 0 !important;
                            padding-bottom: 0 !important;
                        }
                    `;
                    doc.head.appendChild(style);
                }

                Array.from(doc.querySelectorAll('a')).forEach((link) => {
                    const text = (link.textContent || '').trim().toLowerCase();
                    if (!text.includes('cadastro de loja') && !text.includes('cadastro de entregador')) {
                        return;
                    }

                    const block = link.closest('header, nav, div, section');
                    if (block && block.style) {
                        block.style.display = 'none';
                    }
                });

                Array.from(doc.querySelectorAll('div, section, footer, p')).forEach((node) => {
                    const text = (node.textContent || '').trim().toLowerCase();
                    if (
                        text.includes('todos os direitos reservados') ||
                        text.includes('intermediacao de negocios') ||
                        text.includes('cnpj') ||
                        text.includes('rua frei bernardo')
                    ) {
                        const block = node.closest('footer, section, div');
                        if (block && block.style) {
                            block.style.display = 'none';
                        }
                    }
                });
            } catch (error) {
                // Ignore iframe access issues while the official page is loading.
            }
        };

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

        const showCompleteMessage = () => {
            if (!completeMessage || !registrationSection) {
                return;
            }
            registrationSection.style.display = 'none';
            completeMessage.style.display = 'block';
        };

        const checkCompletion = () => {
            try {
                const activeFrame = frameStore.style.display === 'none' ? frameDelivery : frameStore;
                cleanupFrame(activeFrame);
                const currentUrl = activeFrame.contentWindow.location.href;
                if (
                    currentUrl.includes('/vendor/final-step') ||
                    currentUrl.includes('/deliveryman') ||
                    currentUrl.includes('step=complete')
                ) {
                    showCompleteMessage();
                }
            } catch (error) {
                // Ignore iframe access issues while the official page is loading.
            }
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => setActive(button.dataset.target));
        });

        frameStore.addEventListener('load', () => {
            cleanupFrame(frameStore);
            checkCompletion();
        });
        frameDelivery.addEventListener('load', () => {
            cleanupFrame(frameDelivery);
            checkCompletion();
        });
        window.setInterval(() => cleanupFrame(frameStore), 1000);
        window.setInterval(() => cleanupFrame(frameDelivery), 1000);
        window.setInterval(checkCompletion, 1200);

        setActive(<?= json_encode($activeType) ?>);
    })();
</script>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro Parceiros';
$current = 'partners';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
