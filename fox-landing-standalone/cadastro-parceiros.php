<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$activeType = ($_GET['tipo'] ?? 'store') === 'delivery' ? 'delivery' : 'store';
$vendorApplyUrl = sixammart_url('vendor/apply');
$deliveryApplyUrl = sixammart_url('deliveryman/apply');
$copyByType = [
    'store' => [
        'panel_label' => 'Parceiros Fox Delivery',
        'panel_title' => 'Tipo de cadastro',
        'requirements' => [
            'Cadastro da loja e do respons&aacute;vel legal pela opera&ccedil;&atilde;o.',
            'Envio realizado pelo fluxo oficial <code>/vendor/apply</code>, com os mesmos crit&eacute;rios do painel administrativo.',
            'Aprova&ccedil;&atilde;o, status e acompanhamento centralizados na opera&ccedil;&atilde;o da Fox Delivery.',
        ],
        'note_title' => 'Integra&ccedil;&atilde;o oficial',
        'note_body' => 'Os dados da loja s&atilde;o enviados diretamente ao ambiente oficial da Fox Delivery, sem formul&aacute;rios paralelos ou retrabalho operacional.',
    ],
    'delivery' => [
        'panel_label' => 'Parceiros Fox Delivery',
        'panel_title' => 'Tipo de cadastro',
        'requirements' => [
            'Cadastro pessoal, documenta&ccedil;&atilde;o e dados operacionais do entregador.',
            'Envio realizado pelo fluxo oficial <code>/deliveryman/apply</code>, com as mesmas valida&ccedil;&otilde;es do painel administrativo.',
            'Aprova&ccedil;&atilde;o, status e acompanhamento centralizados na opera&ccedil;&atilde;o da Fox Delivery.',
        ],
        'note_title' => 'Integra&ccedil;&atilde;o oficial',
        'note_body' => 'Os dados do entregador s&atilde;o enviados diretamente ao ambiente oficial da Fox Delivery, sem cadastros paralelos ou duplicidade de regras.',
    ],
];
$activeCopy = $copyByType[$activeType];

ob_start();
?>
<section class="hero registration-hero">
    <div class="container registration-hero-content">
        <h1>Cadastro Oficial Fox Delivery</h1>
        <p>Escolha a modalidade de parceria e conclua o cadastro pelo formul&aacute;rio oficial, com integra&ccedil;&atilde;o direta ao painel administrativo da Fox Delivery.</p>
    </div>
</section>

<section class="container section contact registration-layout unified-registration">
    <aside class="panel registration-side">
        <span class="panel-kicker" id="copy-panel-label"><?= $activeCopy['panel_label'] ?></span>
        <h2 id="copy-panel-title"><?= $activeCopy['panel_title'] ?></h2>

        <div class="switcher" role="tablist" aria-label="Tipo de cadastro">
            <button class="switch-btn <?= $activeType === 'store' ? 'active' : '' ?>" data-target="store" role="tab" aria-selected="<?= $activeType === 'store' ? 'true' : 'false' ?>">Loja</button>
            <button class="switch-btn <?= $activeType === 'delivery' ? 'active' : '' ?>" data-target="delivery" role="tab" aria-selected="<?= $activeType === 'delivery' ? 'true' : 'false' ?>">Entregador</button>
        </div>

        <ul class="requirements" id="requirements-list">
            <?php foreach ($activeCopy['requirements'] as $item): ?>
                <li><?= $item ?></li>
            <?php endforeach; ?>
        </ul>

        <div class="sync-note">
            <strong id="copy-note-title"><?= $activeCopy['note_title'] ?></strong>
            <p id="copy-note-body"><?= $activeCopy['note_body'] ?></p>
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
            <iframe class="official-frame" id="frame-store" src="<?= e($vendorApplyUrl) ?>" title="Cadastro oficial de loja" loading="lazy" <?= $activeType === 'store' ? '' : 'style="display:none;"' ?>></iframe>
            <iframe class="official-frame" id="frame-delivery" src="<?= e($deliveryApplyUrl) ?>" title="Cadastro oficial de entregador" loading="lazy" <?= $activeType === 'delivery' ? '' : 'style="display:none;"' ?>></iframe>
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
        const copyByType = <?= json_encode($copyByType) ?>;
        const buttons = document.querySelectorAll('.switch-btn');
        const frameStore = document.getElementById('frame-store');
        const frameDelivery = document.getElementById('frame-delivery');
        const requirementsList = document.getElementById('requirements-list');
        const panelLabel = document.getElementById('copy-panel-label');
        const panelTitle = document.getElementById('copy-panel-title');
        const noteTitle = document.getElementById('copy-note-title');
        const noteBody = document.getElementById('copy-note-body');
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

                const frameType = frame === frameStore ? 'store' : 'delivery';
                if (frameType === 'store') {
                    const normalizeText = (value) => (value || '')
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/\s+/g, ' ')
                        .trim();
                    const stepperBlock = Array.from(doc.querySelectorAll('div, nav, section, ul'))
                        .find((node) => {
                            const text = normalizeText(node.textContent);
                            return text.includes('informacoes gerais') &&
                                text.includes('plano de negocios') &&
                                text.includes('completo') &&
                                text.length < 180;
                        });

                    if (stepperBlock && stepperBlock.style) {
                        stepperBlock.style.display = 'none';
                    }
                }
            } catch (error) {
                // Ignore iframe access issues while the official page is loading.
            }
        };

        const setActive = (type) => {
            const storeActive = type === 'store';
            frameStore.style.display = storeActive ? 'block' : 'none';
            frameDelivery.style.display = storeActive ? 'none' : 'block';

            const copy = copyByType[type];
            panelLabel.innerHTML = copy.panel_label;
            panelTitle.innerHTML = copy.panel_title;
            requirementsList.innerHTML = copy.requirements.map((item) => `<li>${item}</li>`).join('');
            noteTitle.innerHTML = copy.note_title;
            noteBody.innerHTML = copy.note_body;

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

        const hasDeliverySuccessState = (frame) => {
            try {
                const doc = frame.contentDocument || frame.contentWindow.document;
                if (!doc) {
                    return false;
                }

                if (doc.querySelector('.toast-success, #toast-container .toast-success, .alert-success')) {
                    return true;
                }

                const scriptText = Array.from(doc.querySelectorAll('script'))
                    .map((script) => script.textContent || '')
                    .join('\n')
                    .toLowerCase();

                return scriptText.includes('toastr.success');
            } catch (error) {
                return false;
            }
        };

        const checkCompletion = () => {
            try {
                const activeFrame = frameStore.style.display === 'none' ? frameDelivery : frameStore;
                const activeType = frameStore.style.display === 'none' ? 'delivery' : 'store';
                cleanupFrame(activeFrame);
                const currentUrl = activeFrame.contentWindow.location.href;
                if (activeType === 'store' && (currentUrl.includes('/vendor/final-step') || currentUrl.includes('step=complete'))) {
                    showCompleteMessage();
                }

                if (activeType === 'delivery' && currentUrl.includes('/deliveryman/apply') && hasDeliverySuccessState(activeFrame)) {
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
