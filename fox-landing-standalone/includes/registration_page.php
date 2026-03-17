<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function render_registration_page(string $mode = 'store'): void
{
    $isDelivery = $mode === 'delivery';
    $vendorApplyUrl = sixammart_url('vendor/apply');
    $deliveryApplyUrl = sixammart_url('deliveryman/apply');

    $title = $isDelivery ? 'Cadastro de Entregador' : 'Cadastro de Loja';
    $subtitle = $isDelivery
        ? 'Fluxo oficial do entregador com os mesmos campos, validacoes e aprovacao do painel.'
        : 'Fluxo oficial da loja com os mesmos campos, validacoes e aprovacao do painel.';
    $pageTitle = $isDelivery ? 'Fox Delivery - Cadastro de Entregador' : 'Fox Delivery - Cadastro de Loja';
    $frameUrl = $isDelivery ? $deliveryApplyUrl : $vendorApplyUrl;
    $completionMarkers = $isDelivery
        ? ['/deliveryman/apply?step=complete', '/deliveryman']
        : ['/vendor/final-step', '/vendor/apply?step=complete', '/vendor'];

    ob_start();
    ?>
    <section class="hero registration-hero">
        <div class="container registration-hero-content">
            <h1><?= e($title) ?></h1>
            <p><?= e($subtitle) ?></p>
        </div>
    </section>

    <section class="container section contact registration-layout unified-registration">
        <aside class="panel registration-side">
            <span class="panel-kicker">Cadastro oficial</span>
            <h2>Tipo de cadastro</h2>

            <div class="switcher" role="tablist" aria-label="Tipo de cadastro">
                <a class="switch-btn <?= $isDelivery ? '' : 'active' ?>" href="./cadastro-loja.php" role="tab" aria-selected="<?= $isDelivery ? 'false' : 'true' ?>">Loja</a>
                <a class="switch-btn <?= $isDelivery ? 'active' : '' ?>" href="./cadastro-entregador.php" role="tab" aria-selected="<?= $isDelivery ? 'true' : 'false' ?>">Entregador</a>
            </div>

            <ul class="requirements">
                <?php if ($isDelivery): ?>
                    <li>Formulario oficial do entregador sem camada paralela de cadastro.</li>
                    <li>Envio direto para <code>/deliveryman/apply</code> com os mesmos campos do admin.</li>
                    <li>Status, aprovacao e dados gravados no mesmo fluxo do banco principal.</li>
                <?php else: ?>
                    <li>Formulario oficial da loja e do responsavel legal.</li>
                    <li>Envio direto para <code>/vendor/apply</code> com os mesmos campos do admin.</li>
                    <li>Status, aprovacao e dados gravados no mesmo fluxo do banco principal.</li>
                <?php endif; ?>
            </ul>

            <div class="sync-note">
                <strong>Sincronizacao real</strong>
                <p>Esta tela apenas muda o visual. O processamento continua no backend oficial do 6amMart.</p>
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
                <iframe
                    class="official-frame"
                    id="registration-frame"
                    src="<?= e($frameUrl) ?>"
                    title="<?= e($title) ?>"
                    loading="lazy"
                ></iframe>
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
            const frame = document.getElementById('registration-frame');
            const completeMessage = document.getElementById('registration-complete-message');
            const registrationSection = document.querySelector('.registration-layout');
            const completionMarkers = <?= json_encode($completionMarkers) ?>;

            const cleanupFrame = () => {
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

            const showCompleteMessage = () => {
                if (!completeMessage || !registrationSection) {
                    return;
                }
                registrationSection.style.display = 'none';
                completeMessage.style.display = 'block';
            };

            const checkCompletion = () => {
                try {
                    cleanupFrame();
                    const currentUrl = frame.contentWindow.location.href;
                    const reachedCompleteStep = completionMarkers.some((marker) => currentUrl.includes(marker));

                    if (reachedCompleteStep && !currentUrl.includes('/apply')) {
                        showCompleteMessage();
                    }

                    if (currentUrl.includes('final-step') || currentUrl.includes('step=complete')) {
                        showCompleteMessage();
                    }
                } catch (error) {
                    // Ignore iframe access issues while the official page is loading.
                }
            };

            frame.addEventListener('load', () => {
                cleanupFrame();
                checkCompletion();
            });
            window.setInterval(cleanupFrame, 1000);
            window.setInterval(checkCompletion, 1200);
        })();
    </script>
    <?php

    $content = ob_get_clean();
    $current = 'partners';
    $hidePageHeader = true;
    $hidePageFooter = true;
    require __DIR__ . '/layout.php';
}
