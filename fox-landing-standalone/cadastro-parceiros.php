<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/registration_portal.php';

$state = registration_bootstrap();
$activeType = $state['activeType'];
$copyByType = $state['copyByType'];
$activeCopy = $copyByType[$activeType];
$activeForm = $state['forms'][$activeType];

ob_start();
?>
<section class="hero registration-hero">
    <div class="container registration-hero-content">
        <h1>Cadastro Oficial Fox Delivery</h1>
        <p>Escolha a modalidade de parceria e conclua o cadastro em um formul&aacute;rio pr&oacute;prio da Fox Delivery, com integra&ccedil;&atilde;o direta ao painel administrativo.</p>
    </div>
</section>

<section class="container section contact registration-layout unified-registration">
    <aside class="panel registration-side">
        <span class="panel-kicker" id="copy-panel-label"><?= $activeCopy['panel_label'] ?></span>
        <h2 id="copy-panel-title"><?= $activeCopy['panel_title'] ?></h2>

        <div class="switcher" role="tablist" aria-label="Tipo de cadastro">
            <button type="button" class="switch-btn <?= $activeType === 'store' ? 'active' : '' ?>" data-target="store" role="tab" aria-selected="<?= $activeType === 'store' ? 'true' : 'false' ?>">Loja</button>
            <button type="button" class="switch-btn <?= $activeType === 'delivery' ? 'active' : '' ?>" data-target="delivery" role="tab" aria-selected="<?= $activeType === 'delivery' ? 'true' : 'false' ?>">Entregador</button>
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

    <div class="panel embedded-panel registration-frame-shell registration-form-shell">
        <div class="frame-title">
            <span>Painel</span>
            <strong>Fox Delivery</strong>
        </div>

        <div class="frame-steps" aria-hidden="true">
            <span class="frame-step active">Informa&ccedil;&otilde;es iniciais</span>
            <span class="frame-step">Valida&ccedil;&atilde;o cadastral</span>
            <span class="frame-step">Conclus&atilde;o</span>
        </div>

        <?php if (!empty($activeForm['success'])): ?>
            <?= registration_render_success_message((string) $activeForm['message']) ?>
        <?php else: ?>
            <div class="registration-form-panels">
                <section class="registration-form-panel <?= $activeType === 'store' ? 'active' : '' ?>" data-panel-type="store">
                    <?= registration_render_store_form($state['forms']['store'], $state['catalog'], $state['settings']) ?>
                </section>
                <section class="registration-form-panel <?= $activeType === 'delivery' ? 'active' : '' ?>" data-panel-type="delivery">
                    <?= registration_render_delivery_form($state['forms']['delivery'], $state['catalog'], $state['settings']) ?>
                </section>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= registration_render_scripts($state['catalog']) ?>

<script>
    (function () {
        const copyByType = <?= json_encode($copyByType) ?>;
        const buttons = document.querySelectorAll('.switch-btn[data-target]');
        const panels = document.querySelectorAll('.registration-form-panel');
        const requirementsList = document.getElementById('requirements-list');
        const panelLabel = document.getElementById('copy-panel-label');
        const panelTitle = document.getElementById('copy-panel-title');
        const noteTitle = document.getElementById('copy-note-title');
        const noteBody = document.getElementById('copy-note-body');

        const setActive = (type) => {
            const copy = copyByType[type];
            if (!copy) {
                return;
            }

            panelLabel.innerHTML = copy.panel_label;
            panelTitle.innerHTML = copy.panel_title;
            requirementsList.innerHTML = copy.requirements.map((item) => `<li>${item}</li>`).join('');
            noteTitle.innerHTML = copy.note_title;
            noteBody.innerHTML = copy.note_body;

            panels.forEach((panel) => {
                panel.classList.toggle('active', panel.dataset.panelType === type);
            });

            buttons.forEach((button) => {
                const active = button.dataset.target === type;
                button.classList.toggle('active', active);
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            const url = new URL(window.location.href);
            url.searchParams.set('tipo', type);
            window.history.replaceState({}, '', url.toString());
        };

        buttons.forEach((button) => {
            button.addEventListener('click', () => setActive(button.dataset.target));
        });
    })();
</script>
<?php

$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro Parceiros';
$current = 'partners';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
