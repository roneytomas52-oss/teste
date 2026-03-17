<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function render_registration_page(string $mode = 'store'): void
{
    $isDelivery = $mode === 'delivery';
    $vendorApplyUrl = sixammart_url('vendor/apply');
    $deliveryApplyUrl = sixammart_url('deliveryman/apply');
    $copy = $isDelivery ? [
        'title' => 'Cadastro de Entregador Fox Delivery',
        'subtitle' => 'Preencha o formul&aacute;rio oficial do entregador com an&aacute;lise cadastral, valida&ccedil;&atilde;o documental e acompanhamento pelo painel administrativo da Fox Delivery.',
        'panel_label' => 'Parceiros Fox Delivery',
        'panel_title' => 'Modalidade de cadastro',
        'requirements' => [
            'Cadastro pessoal, documenta&ccedil;&atilde;o e dados operacionais do entregador.',
            'Envio realizado pelo fluxo oficial <code>/deliveryman/apply</code>, com as mesmas valida&ccedil;&otilde;es do painel administrativo.',
            'Aprova&ccedil;&atilde;o, status e acompanhamento centralizados na opera&ccedil;&atilde;o da Fox Delivery.',
        ],
        'note_title' => 'Integra&ccedil;&atilde;o oficial',
        'note_body' => 'Os dados do entregador s&atilde;o enviados diretamente ao ambiente oficial da Fox Delivery, sem cadastros paralelos ou duplicidade de regras.',
    ] : [
        'title' => 'Cadastro de Loja Fox Delivery',
        'subtitle' => 'Preencha o formul&aacute;rio oficial do lojista com valida&ccedil;&atilde;o cadastral, confer&ecirc;ncia documental e integra&ccedil;&atilde;o direta ao painel administrativo da Fox Delivery.',
        'panel_label' => 'Parceiros Fox Delivery',
        'panel_title' => 'Modalidade de cadastro',
        'requirements' => [
            'Cadastro da loja e do respons&aacute;vel legal pela opera&ccedil;&atilde;o.',
            'Envio realizado pelo fluxo oficial <code>/vendor/apply</code>, com os mesmos crit&eacute;rios do painel administrativo.',
            'Aprova&ccedil;&atilde;o, status e acompanhamento centralizados na opera&ccedil;&atilde;o da Fox Delivery.',
        ],
        'note_title' => 'Integra&ccedil;&atilde;o oficial',
        'note_body' => 'Os dados da loja s&atilde;o enviados diretamente ao ambiente oficial da Fox Delivery, sem formul&aacute;rios paralelos ou retrabalho operacional.',
    ];
    $title = $copy['title'];
    $subtitle = $copy['subtitle'];
    $pageTitle = $isDelivery ? 'Fox Delivery - Cadastro de Entregador' : 'Fox Delivery - Cadastro de Loja';
    $frameUrl = $isDelivery ? $deliveryApplyUrl : $vendorApplyUrl;
    $completionMarkers = $isDelivery
        ? ['/deliveryman/apply?step=complete', '/deliveryman']
        : ['/vendor/final-step', '/vendor/apply?step=complete', '/vendor'];

    ob_start();
    ?>
    <section class="hero registration-hero">
        <div class="container registration-hero-content">
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
    </section>

    <section class="container section contact registration-layout unified-registration">
        <aside class="panel registration-side">
            <span class="panel-kicker"><?= $copy['panel_label'] ?></span>
            <h2><?= $copy['panel_title'] ?></h2>

            <div class="switcher" role="tablist" aria-label="Tipo de cadastro">
                <a class="switch-btn <?= $isDelivery ? '' : 'active' ?>" href="./cadastro-loja.php" role="tab" aria-selected="<?= $isDelivery ? 'false' : 'true' ?>">Loja</a>
                <a class="switch-btn <?= $isDelivery ? 'active' : '' ?>" href="./cadastro-entregador.php" role="tab" aria-selected="<?= $isDelivery ? 'true' : 'false' ?>">Entregador</a>
            </div>

            <ul class="requirements">
                <?php foreach ($copy['requirements'] as $item): ?>
                    <li><?= $item ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="sync-note">
                <strong><?= $copy['note_title'] ?></strong>
                <p><?= $copy['note_body'] ?></p>
            </div>
        </aside>

        <div class="panel embedded-panel registration-frame-shell">
            <div class="frame-title">
                <span>Painel</span>
                <strong>Fox Delivery</strong>
            </div>

            <div class="frame-steps" aria-hidden="true">
                <span class="frame-step active">Informa&ccedil;&otilde;es iniciais</span>
                <span class="frame-step">Valida&ccedil;&atilde;o cadastral</span>
                <span class="frame-step">Conclus&atilde;o</span>
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
            <h2>Cadastro enviado com sucesso</h2>
            <p>Seu cadastro foi conclu&iacute;do no fluxo oficial e j&aacute; est&aacute; sincronizado com o painel administrativo da Fox Delivery.</p>
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
