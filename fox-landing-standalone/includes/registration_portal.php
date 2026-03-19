<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/validators.php';

function registration_copy_map(): array
{
    return [
        'store' => [
            'panel_label' => 'Parceiros Fox Delivery',
            'panel_title' => 'Tipo de cadastro',
            'requirements' => [
                'Cadastro da loja e do respons&aacute;vel legal pela opera&ccedil;&atilde;o.',
                'Campos equivalentes ao fluxo oficial <code>/vendor/apply</code>.',
                'Status, aprova&ccedil;&atilde;o e acompanhamento vinculados ao painel administrativo.',
            ],
            'note_title' => 'Integra&ccedil;&atilde;o oficial',
            'note_body' => 'Este formul&aacute;rio &eacute; pr&oacute;prio da Fox Delivery, mas envia os dados para as APIs oficiais do painel, sem duplicar regras locais.',
        ],
        'delivery' => [
            'panel_label' => 'Parceiros Fox Delivery',
            'panel_title' => 'Tipo de cadastro',
            'requirements' => [
                'Cadastro pessoal, documenta&ccedil;&atilde;o e dados operacionais do entregador.',
                'Campos equivalentes ao fluxo oficial <code>/deliveryman/apply</code>.',
                'Status, aprova&ccedil;&atilde;o e acompanhamento vinculados ao painel administrativo.',
            ],
            'note_title' => 'Integra&ccedil;&atilde;o oficial',
            'note_body' => 'Este formul&aacute;rio &eacute; pr&oacute;prio da Fox Delivery, mas envia os dados para as APIs oficiais do painel, mantendo as mesmas exig&ecirc;ncias cadastrais.',
        ],
    ];
}

function registration_bootstrap(?string $forcedType = null): array
{
    $catalog = registration_catalog();
    $settings = registration_settings();
    $syncStatus = registration_sync_status();
    $copyByType = registration_copy_map();
    $flash = registration_consume_flash();

    $defaultType = $forcedType !== null
        ? registration_normalize_type($forcedType)
        : registration_normalize_type((string) ($_GET['tipo'] ?? data_get($flash, 'type', 'store')));

    $forms = [
        'store' => registration_empty_state('store'),
        'delivery' => registration_empty_state('delivery'),
    ];

    if ($flash !== null) {
        $flashType = registration_normalize_type((string) ($flash['type'] ?? $defaultType));
        $forms[$flashType]['success'] = true;
        $forms[$flashType]['message'] = (string) ($flash['message'] ?? '');
        $forms[$flashType]['response'] = is_array($flash['response'] ?? null) ? $flash['response'] : [];
        $defaultType = $flashType;
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $submittedType = $forcedType !== null
            ? registration_normalize_type($forcedType)
            : registration_normalize_type((string) ($_POST['registration_type'] ?? $defaultType));

        $defaultType = $submittedType;
        $forms[$submittedType] = $submittedType === 'delivery'
            ? registration_process_delivery($catalog, $settings)
            : registration_process_store($catalog, $settings);

        if ($forms[$submittedType]['success']) {
            registration_store_flash($submittedType, (string) $forms[$submittedType]['message'], $forms[$submittedType]['response']);
            registration_redirect_after_submit($submittedType);
        }
    }

    return [
        'catalog' => $catalog,
        'settings' => $settings,
        'syncStatus' => $syncStatus,
        'copyByType' => $copyByType,
        'activeType' => $defaultType,
        'forms' => $forms,
    ];
}

function registration_render_page(string $mode = 'store'): void
{
    $state = registration_bootstrap($mode);
    $copy = $state['copyByType'][$mode];
    $title = $mode === 'delivery' ? 'Cadastro de Entregador Fox Delivery' : 'Cadastro de Loja Fox Delivery';
    $subtitle = $mode === 'delivery'
        ? 'Jornada progressiva para entrada do entregador na opera&ccedil;&atilde;o Fox Delivery, com etapas de perfil, identifica&ccedil;&atilde;o, opera&ccedil;&atilde;o e envio para an&aacute;lise.'
        : 'Jornada progressiva para cadastro da loja parceira, com etapas de respons&aacute;vel, opera&ccedil;&atilde;o, documentos e envio para an&aacute;lise da Fox Delivery.';
    $pageTitle = $mode === 'delivery' ? 'Fox Delivery - Cadastro de Entregador' : 'Fox Delivery - Cadastro de Loja';
    $formState = $state['forms'][$mode];
    $pageSteps = $mode === 'store'
        ? ['Respons&aacute;vel', 'Dados da loja', 'Opera&ccedil;&atilde;o', 'Documentos', 'Confirma&ccedil;&atilde;o']
        : ['Perfil', 'Identifica&ccedil;&atilde;o', 'Opera&ccedil;&atilde;o', 'Documentos', 'Confirma&ccedil;&atilde;o'];

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
                <a class="switch-btn <?= $mode === 'store' ? 'active' : '' ?>" href="./cadastro-loja.php" role="tab" aria-selected="<?= $mode === 'store' ? 'true' : 'false' ?>">Loja</a>
                <a class="switch-btn <?= $mode === 'delivery' ? 'active' : '' ?>" href="./cadastro-entregador.php" role="tab" aria-selected="<?= $mode === 'delivery' ? 'true' : 'false' ?>">Entregador</a>
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

        <div class="panel embedded-panel registration-frame-shell registration-form-shell">
            <div class="frame-title">
                <span>Painel</span>
                <strong>Fox Delivery</strong>
            </div>

            <div class="frame-steps" aria-hidden="true">
                <?php foreach ($pageSteps as $index => $label): ?>
                    <span class="frame-step <?= $index === 0 ? 'active' : '' ?>"><?= $label ?></span>
                <?php endforeach; ?>
            </div>

            <?php if (!$state['syncStatus']['is_ready']): ?>
                <?= registration_render_alerts([], $state['syncStatus']['issues']) ?>
            <?php endif; ?>

            <?= registration_render_form_panel($mode, $formState, $state['catalog'], $state['settings']) ?>
        </div>
    </section>

    <?= registration_render_scripts($state['catalog']) ?>
    <?php

    $content = ob_get_clean();
    $current = 'partners';
    $hidePageHeader = true;
    $hidePageFooter = true;
    require __DIR__ . '/layout.php';
}

function registration_render_form_panel(string $type, array $formState, array $catalog, array $settings): string
{
    if (!empty($formState['success'])) {
        $message = $formState['message'] ?: 'Cadastro enviado com sucesso.';
        return registration_render_success_message($message);
    }

    return $type === 'delivery'
        ? registration_render_delivery_form($formState, $catalog, $settings)
        : registration_render_store_form($formState, $catalog, $settings);
}

function registration_render_success_message(string $message): string
{
    ob_start();
    ?>
    <div class="registration-success-card">
        <span class="form-stage-tag success">Cadastro conclu&iacute;do</span>
        <h3>Cadastro enviado com sucesso</h3>
        <p><?= $message ?></p>
        <div class="success-badges">
            <span>Status em an&aacute;lise</span>
            <span>Painel sincronizado</span>
            <span>Fox Delivery</span>
        </div>
    </div>
    <?php

    return (string) ob_get_clean();
}

function registration_render_store_form(array $formState, array $catalog, array $settings): string
{
    $data = $formState['data'];
    $selectedZoneId = (string) $data['zone_id'];
    $modules = registration_modules_for_zone($catalog, $selectedZoneId);
    $selectedModuleType = registration_module_type($catalog, (string) $data['module_id']);
    $businessPlan = registration_resolve_business_plan((string) $data['business_plan'], $settings);
    $isDisabled = !$settings['store_enabled'];

    ob_start();
    ?>
    <form class="fox-registration-form fox-store-onboarding" method="post" enctype="multipart/form-data" novalidate data-registration-form="store" data-store-wizard="true" data-track-form="store_registration">
        <input type="hidden" name="registration_type" value="store">

        <div class="form-stage-head">
            <span class="form-stage-tag">Loja parceira</span>
            <h3>Onboarding da loja parceira</h3>
            <p>Conclua a jornada em etapas, com o mesmo padr&atilde;o de dados exigido no fluxo administrativo da Fox Delivery.</p>
        </div>

        <?= registration_render_alerts($formState['errors'], $isDisabled ? ['O cadastro de loja est&aacute; temporariamente indispon&iacute;vel no painel administrativo.'] : []) ?>

        <div class="store-wizard-shell">
            <div class="store-stepper" aria-label="Etapas do cadastro de loja">
                <button type="button" class="store-step-chip is-active" data-store-step-indicator="1">
                    <small>Etapa 1</small>
                    <strong>Respons&aacute;vel</strong>
                </button>
                <button type="button" class="store-step-chip" data-store-step-indicator="2">
                    <small>Etapa 2</small>
                    <strong>Dados da loja</strong>
                </button>
                <button type="button" class="store-step-chip" data-store-step-indicator="3">
                    <small>Etapa 3</small>
                    <strong>Opera&ccedil;&atilde;o</strong>
                </button>
                <button type="button" class="store-step-chip" data-store-step-indicator="4">
                    <small>Etapa 4</small>
                    <strong>Documentos</strong>
                </button>
                <button type="button" class="store-step-chip" data-store-step-indicator="5">
                    <small>Etapa 5</small>
                    <strong>Confirma&ccedil;&atilde;o</strong>
                </button>
            </div>

            <div class="registration-form-sections store-wizard-sections <?= $isDisabled ? 'is-disabled' : '' ?>">
                <section class="form-section store-wizard-step is-active" data-store-step="1">
                    <div class="store-step-head">
                        <span class="store-step-tag">Etapa 1</span>
                        <div class="store-step-copy">
                            <h4>Respons&aacute;vel legal e acesso</h4>
                            <p>Dados do titular respons&aacute;vel pela parceria e pelo acesso administrativo da loja.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Primeiro nome *</span>
                            <input type="text" name="f_name" value="<?= e((string) $data['f_name']) ?>" required>
                        </label>
                        <label>
                            <span>Sobrenome</span>
                            <input type="text" name="l_name" value="<?= e((string) $data['l_name']) ?>">
                        </label>
                        <label>
                            <span>E-mail *</span>
                            <input type="email" name="email" value="<?= e((string) $data['email']) ?>" required>
                        </label>
                        <label>
                            <span>Telefone *</span>
                            <input type="tel" name="phone" value="<?= e((string) $data['phone']) ?>" required>
                        </label>
                        <label class="full">
                            <span>Senha de acesso *</span>
                            <input type="password" name="password" minlength="8" required>
                            <small>M&iacute;nimo de 8 caracteres para acesso ao ambiente da opera&ccedil;&atilde;o.</small>
                        </label>
                    </div>
                    <div class="store-step-actions">
                        <span class="store-step-note">Pr&oacute;xima etapa: dados principais da loja.</span>
                        <button type="button" class="btn" data-store-next="2">Continuar</button>
                    </div>
                </section>

                <section class="form-section store-wizard-step" data-store-step="2">
                    <div class="store-step-head">
                        <span class="store-step-tag">Etapa 2</span>
                        <div class="store-step-copy">
                            <h4>Dados principais da loja</h4>
                            <p>Informa&ccedil;&otilde;es centrais da opera&ccedil;&atilde;o comercial e enquadramento do neg&oacute;cio.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Nome da loja *</span>
                            <input type="text" name="store_name" value="<?= e((string) $data['store_name']) ?>" required>
                        </label>
                        <label>
                            <span>Endere&ccedil;o principal *</span>
                            <input type="text" name="address" value="<?= e((string) $data['address']) ?>" required>
                        </label>
                        <label>
                            <span>Zona de atendimento *</span>
                            <select name="zone_id" data-role="zone-select" required>
                                <option value="">Selecione a zona</option>
                                <?php foreach ($catalog['zones'] as $zone): ?>
                                    <option value="<?= e((string) $zone['id']) ?>" <?= $selectedZoneId === (string) $zone['id'] ? 'selected' : '' ?>><?= e((string) $zone['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>M&oacute;dulo de neg&oacute;cio *</span>
                            <select name="module_id" data-role="module-select" data-selected="<?= e((string) $data['module_id']) ?>" required>
                                <option value=""><?= $selectedZoneId === '' ? 'Selecione primeiro a zona' : 'Selecione o m&oacute;dulo' ?></option>
                                <?php foreach ($modules as $module): ?>
                                    <option
                                        value="<?= e((string) $module['id']) ?>"
                                        data-module-type="<?= e((string) $module['module_type']) ?>"
                                        <?= (string) $data['module_id'] === (string) $module['id'] ? 'selected' : '' ?>
                                    ><?= e((string) $module['module_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label class="full <?= $selectedModuleType === 'rental' ? '' : 'is-hidden' ?>" data-role="pickup-zone-group">
                            <span>Zonas de coleta *</span>
                            <select name="pickup_zone_id[]" multiple size="4" data-role="pickup-zone-select">
                                <?php foreach ($catalog['zones'] as $zone): ?>
                                    <option value="<?= e((string) $zone['id']) ?>" <?= in_array((string) $zone['id'], $data['pickup_zone_id'], true) ? 'selected' : '' ?>><?= e((string) $zone['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small>Necess&aacute;rio para m&oacute;dulos do tipo locadora.</small>
                        </label>
                    </div>
                    <div class="store-step-actions">
                        <button type="button" class="btn ghost" data-store-prev="1">Voltar</button>
                        <button type="button" class="btn" data-store-next="3">Continuar</button>
                    </div>
                </section>

                <section class="form-section store-wizard-step" data-store-step="3">
                    <div class="store-step-head">
                        <span class="store-step-tag">Etapa 3</span>
                        <div class="store-step-copy">
                            <h4>Opera&ccedil;&atilde;o e plano comercial</h4>
                            <p>Configura&ccedil;&otilde;es operacionais, localiza&ccedil;&atilde;o e modelo comercial da loja.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Latitude *</span>
                            <input type="number" step="any" name="latitude" value="<?= e((string) $data['latitude']) ?>" required>
                        </label>
                        <label>
                            <span>Longitude *</span>
                            <input type="number" step="any" name="longitude" value="<?= e((string) $data['longitude']) ?>" required>
                        </label>
                        <label>
                            <span>Tempo m&iacute;nimo de entrega *</span>
                            <input type="number" min="1" name="minimum_delivery_time" value="<?= e((string) $data['minimum_delivery_time']) ?>" required>
                        </label>
                        <label>
                            <span>Tempo m&aacute;ximo de entrega *</span>
                            <input type="number" min="1" name="maximum_delivery_time" value="<?= e((string) $data['maximum_delivery_time']) ?>" required>
                        </label>
                        <label class="full">
                            <span>Unidade do prazo *</span>
                            <select name="delivery_time_type" required>
                                <option value="min" <?= (string) $data['delivery_time_type'] === 'min' ? 'selected' : '' ?>>Minutos</option>
                                <option value="hour" <?= (string) $data['delivery_time_type'] === 'hour' ? 'selected' : '' ?>>Horas</option>
                            </select>
                        </label>
                    </div>

                    <?php if ($settings['commission_enabled'] || $settings['subscription_enabled']): ?>
                        <div class="store-plan-shell">
                            <div class="form-section-heading">
                                <h4>Plano comercial</h4>
                                <p>Defina o modelo de opera&ccedil;&atilde;o comercial para vincular o cadastro ao fluxo correto.</p>
                            </div>
                            <div class="plan-choice-grid">
                                <?php if ($settings['commission_enabled']): ?>
                                    <label class="plan-choice <?= $businessPlan === 'commission' ? 'active' : '' ?>">
                                        <input type="radio" name="business_plan" value="commission" <?= $businessPlan === 'commission' ? 'checked' : '' ?>>
                                        <strong>Comiss&atilde;o</strong>
                                        <span>A loja opera com repasse por pedido e acompanhamento pelo painel da Fox Delivery.</span>
                                    </label>
                                <?php endif; ?>
                                <?php if ($settings['subscription_enabled']): ?>
                                    <label class="plan-choice <?= $businessPlan === 'subscription' ? 'active' : '' ?>">
                                        <input type="radio" name="business_plan" value="subscription" <?= $businessPlan === 'subscription' ? 'checked' : '' ?>>
                                        <strong>Assinatura</strong>
                                        <span>Selecione um pacote ativo para vincular o cadastro ao modelo de assinatura do painel.</span>
                                    </label>
                                <?php endif; ?>
                            </div>

                            <?php if ($settings['subscription_enabled'] && !empty($catalog['packages'])): ?>
                                <div class="package-grid <?= $businessPlan === 'subscription' ? '' : 'is-hidden' ?>" data-role="package-group">
                                    <?php foreach ($catalog['packages'] as $package): ?>
                                        <?php
                                        $packageType = (string) $package['module_type'];
                                        $hiddenClass = registration_should_hide_package($packageType, $selectedModuleType) ? 'is-hidden' : '';
                                        ?>
                                        <label class="package-card <?= (string) $data['package_id'] === (string) $package['id'] ? 'active' : '' ?> <?= $hiddenClass ?>" data-module-type="<?= e($packageType) ?>">
                                            <input type="radio" name="package_id" value="<?= e((string) $package['id']) ?>" <?= (string) $data['package_id'] === (string) $package['id'] ? 'checked' : '' ?>>
                                            <div class="package-card-body">
                                                <strong><?= e((string) $package['package_name']) ?></strong>
                                                <span class="package-price"><?= registration_format_price((float) $package['price'], $settings) ?></span>
                                                <span class="package-meta"><?= e((string) $package['validity']) ?> dias de vig&ecirc;ncia</span>
                                                <ul>
                                                    <?php foreach (registration_package_features($package) as $feature): ?>
                                                        <li><?= $feature ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="store-step-actions">
                        <button type="button" class="btn ghost" data-store-prev="2">Voltar</button>
                        <button type="button" class="btn" data-store-next="4">Continuar</button>
                    </div>
                </section>

                <section class="form-section store-wizard-step" data-store-step="4">
                    <div class="store-step-head">
                        <span class="store-step-tag">Etapa 4</span>
                        <div class="store-step-copy">
                            <h4>Documentos e imagens</h4>
                            <p>Arquivos utilizados na confer&ecirc;ncia cadastral da loja dentro do painel administrativo.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Logo da loja *</span>
                            <input type="file" name="logo" accept="image/*" <?= $isDisabled ? 'disabled' : '' ?> required>
                        </label>
                        <label>
                            <span>Capa da loja</span>
                            <input type="file" name="cover_photo" accept="image/*" <?= $isDisabled ? 'disabled' : '' ?>>
                        </label>
                        <label>
                            <span>CNPJ / TIN</span>
                            <input type="text" name="tin" value="<?= e((string) $data['tin']) ?>">
                        </label>
                        <label>
                            <span>Validade do documento fiscal</span>
                            <input type="date" name="tin_expire_date" value="<?= e((string) $data['tin_expire_date']) ?>">
                        </label>
                        <label class="full">
                            <span>Certificado fiscal</span>
                            <input type="file" name="tin_certificate_image" accept="image/*,.pdf" <?= $isDisabled ? 'disabled' : '' ?>>
                        </label>
                    </div>
                    <div class="store-step-actions">
                        <button type="button" class="btn ghost" data-store-prev="3">Voltar</button>
                        <button type="button" class="btn" data-store-next="5">Continuar</button>
                    </div>
                </section>

                <section class="form-section store-wizard-step" data-store-step="5">
                    <div class="store-step-head">
                        <span class="store-step-tag">Etapa 5</span>
                        <div class="store-step-copy">
                            <h4>Confirma&ccedil;&atilde;o e envio</h4>
                            <p>Revise os pontos principais da jornada e envie o cadastro para an&aacute;lise da equipe Fox Delivery.</p>
                        </div>
                    </div>
                    <div class="store-review-card">
                        <strong>O que segue para an&aacute;lise</strong>
                        <ul class="store-review-list">
                            <li>Dados do respons&aacute;vel legal e credenciais de acesso</li>
                            <li>Identifica&ccedil;&atilde;o da loja, zona e m&oacute;dulo de neg&oacute;cio</li>
                            <li>Configura&ccedil;&otilde;es operacionais e plano comercial</li>
                            <li>Logo, documentos e arquivos complementares do cadastro</li>
                        </ul>
                        <p>Ap&oacute;s o envio, a Fox Delivery inicia a confer&ecirc;ncia das informa&ccedil;&otilde;es para orientar a pr&oacute;xima etapa da ativa&ccedil;&atilde;o.</p>
                    </div>
                    <div class="store-step-actions">
                        <button type="button" class="btn ghost" data-store-prev="4">Voltar</button>
                        <button type="submit" class="btn" data-track="store_registration_submit_click" data-track-component="registration_form" <?= $isDisabled ? 'disabled' : '' ?>>Enviar cadastro da loja</button>
                    </div>
                </section>
            </div>
        </div>
    </form>
    <?php

    return (string) ob_get_clean();
}

function registration_render_delivery_form(array $formState, array $catalog, array $settings): string
{
    $data = $formState['data'];
    $isDisabled = !$settings['delivery_enabled'];

    ob_start();
    ?>
    <form class="fox-registration-form fox-delivery-onboarding" method="post" enctype="multipart/form-data" novalidate data-registration-form="delivery" data-delivery-wizard="true" data-track-form="delivery_registration">
        <input type="hidden" name="registration_type" value="delivery">

        <div class="form-stage-head">
            <span class="form-stage-tag">Entregador parceiro</span>
            <h3>Onboarding operacional do entregador</h3>
            <p>Conclua a jornada em etapas para enviar os dados exigidos na entrada do entregador para a opera&ccedil;&atilde;o Fox Delivery.</p>
        </div>

        <?= registration_render_alerts($formState['errors'], $isDisabled ? ['O cadastro de entregador est&aacute; temporariamente indispon&iacute;vel no painel administrativo.'] : []) ?>

        <div class="delivery-wizard-shell">
            <div class="delivery-stepper" aria-label="Etapas do cadastro do entregador">
                <button type="button" class="delivery-step-chip is-active" data-delivery-step-indicator="1">
                    <small>Etapa 1</small>
                    <strong>Perfil</strong>
                </button>
                <button type="button" class="delivery-step-chip" data-delivery-step-indicator="2">
                    <small>Etapa 2</small>
                    <strong>Identifica&ccedil;&atilde;o</strong>
                </button>
                <button type="button" class="delivery-step-chip" data-delivery-step-indicator="3">
                    <small>Etapa 3</small>
                    <strong>Opera&ccedil;&atilde;o</strong>
                </button>
                <button type="button" class="delivery-step-chip" data-delivery-step-indicator="4">
                    <small>Etapa 4</small>
                    <strong>Documentos</strong>
                </button>
                <button type="button" class="delivery-step-chip" data-delivery-step-indicator="5">
                    <small>Etapa 5</small>
                    <strong>Confirma&ccedil;&atilde;o</strong>
                </button>
            </div>

            <div class="registration-form-sections delivery-wizard-sections <?= $isDisabled ? 'is-disabled' : '' ?>">
                <section class="form-section delivery-wizard-step is-active" data-delivery-step="1">
                    <div class="delivery-step-head">
                        <span class="delivery-step-tag">Etapa 1</span>
                        <div class="delivery-step-copy">
                            <h4>Perfil e acesso</h4>
                            <p>Dados principais do entregador para contato e acesso ao ambiente operacional.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Primeiro nome *</span>
                            <input type="text" name="f_name" value="<?= e((string) $data['f_name']) ?>" required>
                        </label>
                        <label>
                            <span>Sobrenome</span>
                            <input type="text" name="l_name" value="<?= e((string) $data['l_name']) ?>">
                        </label>
                        <label>
                            <span>E-mail *</span>
                            <input type="email" name="email" value="<?= e((string) $data['email']) ?>" required>
                        </label>
                        <label>
                            <span>Telefone *</span>
                            <input type="tel" name="phone" value="<?= e((string) $data['phone']) ?>" required>
                        </label>
                        <label class="full">
                            <span>Senha de acesso *</span>
                            <input type="password" name="password" minlength="8" required>
                            <small>M&iacute;nimo de 8 caracteres para acesso ao ambiente do entregador.</small>
                        </label>
                    </div>
                    <div class="delivery-step-actions">
                        <span class="delivery-step-note">Pr&oacute;xima etapa: identifica&ccedil;&atilde;o do entregador.</span>
                        <button type="button" class="btn" data-delivery-next="2">Continuar</button>
                    </div>
                </section>

                <section class="form-section delivery-wizard-step" data-delivery-step="2">
                    <div class="delivery-step-head">
                        <span class="delivery-step-tag">Etapa 2</span>
                        <div class="delivery-step-copy">
                            <h4>Identifica&ccedil;&atilde;o</h4>
                            <p>Informa&ccedil;&otilde;es do documento utilizadas no processo de valida&ccedil;&atilde;o e credenciamento do entregador.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Tipo de documento *</span>
                            <select name="identity_type" required>
                                <option value="nid" <?= (string) $data['identity_type'] === 'nid' ? 'selected' : '' ?>>RG / documento nacional</option>
                                <option value="driving_license" <?= (string) $data['identity_type'] === 'driving_license' ? 'selected' : '' ?>>CNH</option>
                                <option value="passport" <?= (string) $data['identity_type'] === 'passport' ? 'selected' : '' ?>>Passaporte</option>
                            </select>
                        </label>
                        <label>
                            <span>N&uacute;mero do documento *</span>
                            <input type="text" name="identity_number" value="<?= e((string) $data['identity_number']) ?>" required>
                        </label>
                        <label class="full">
                            <span>C&oacute;digo de refer&ecirc;ncia</span>
                            <input type="text" name="referral_code" value="<?= e((string) $data['referral_code']) ?>">
                            <small>Campo opcional para entrada por indica&ccedil;&atilde;o ou campanha operacional.</small>
                        </label>
                    </div>
                    <div class="delivery-step-actions">
                        <button type="button" class="btn ghost" data-delivery-prev="1">Voltar</button>
                        <button type="button" class="btn" data-delivery-next="3">Continuar</button>
                    </div>
                </section>

                <section class="form-section delivery-wizard-step" data-delivery-step="3">
                    <div class="delivery-step-head">
                        <span class="delivery-step-tag">Etapa 3</span>
                        <div class="delivery-step-copy">
                            <h4>Opera&ccedil;&atilde;o e modalidade</h4>
                            <p>Defina a zona de atendimento, o modelo de remunera&ccedil;&atilde;o e a modalidade operacional do entregador.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Zona de atendimento *</span>
                            <select name="zone_id" required>
                                <option value="">Selecione a zona</option>
                                <?php foreach ($catalog['zones'] as $zone): ?>
                                    <option value="<?= e((string) $zone['id']) ?>" <?= (string) $data['zone_id'] === (string) $zone['id'] ? 'selected' : '' ?>><?= e((string) $zone['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            <span>Modelo de remunera&ccedil;&atilde;o *</span>
                            <select name="earning" required>
                                <option value="1" <?= (string) $data['earning'] === '1' ? 'selected' : '' ?>>Freelancer</option>
                                <option value="0" <?= (string) $data['earning'] === '0' ? 'selected' : '' ?>>Assalariado</option>
                            </select>
                        </label>
                        <label class="full">
                            <span>Modalidade / ve&iacute;culo *</span>
                            <select name="vehicle_id" required>
                                <option value="">Selecione o ve&iacute;culo</option>
                                <?php foreach ($catalog['vehicles'] as $vehicle): ?>
                                    <option value="<?= e((string) $vehicle['id']) ?>" <?= (string) $data['vehicle_id'] === (string) $vehicle['id'] ? 'selected' : '' ?>><?= e((string) $vehicle['type']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small>Escolha a modalidade operacional dispon&iacute;vel para sua atua&ccedil;&atilde;o.</small>
                        </label>
                    </div>
                    <div class="delivery-step-actions">
                        <button type="button" class="btn ghost" data-delivery-prev="2">Voltar</button>
                        <button type="button" class="btn" data-delivery-next="4">Continuar</button>
                    </div>
                </section>

                <section class="form-section delivery-wizard-step" data-delivery-step="4">
                    <div class="delivery-step-head">
                        <span class="delivery-step-tag">Etapa 4</span>
                        <div class="delivery-step-copy">
                            <h4>Documenta&ccedil;&atilde;o visual</h4>
                            <p>Arquivos complementares utilizados pela equipe da Fox Delivery na confer&ecirc;ncia do cadastro.</p>
                        </div>
                    </div>
                    <div class="form-grid">
                        <label>
                            <span>Foto do entregador</span>
                            <input type="file" name="image" accept="image/*" <?= $isDisabled ? 'disabled' : '' ?>>
                        </label>
                        <label>
                            <span>Imagens do documento</span>
                            <input type="file" name="identity_image[]" accept="image/*" multiple <?= $isDisabled ? 'disabled' : '' ?>>
                        </label>
                    </div>
                    <div class="delivery-step-actions">
                        <button type="button" class="btn ghost" data-delivery-prev="3">Voltar</button>
                        <button type="button" class="btn" data-delivery-next="5">Continuar</button>
                    </div>
                </section>

                <section class="form-section delivery-wizard-step" data-delivery-step="5">
                    <div class="delivery-step-head">
                        <span class="delivery-step-tag">Etapa 5</span>
                        <div class="delivery-step-copy">
                            <h4>Confirma&ccedil;&atilde;o e envio</h4>
                            <p>Revise os dados principais da jornada do entregador antes de enviar o cadastro para an&aacute;lise.</p>
                        </div>
                    </div>
                    <div class="delivery-review-card">
                        <strong>O que segue para an&aacute;lise</strong>
                        <ul class="delivery-review-list">
                            <li>Perfil e credenciais de acesso do entregador</li>
                            <li>Documento e identifica&ccedil;&atilde;o principal</li>
                            <li>Zona de atendimento, remunera&ccedil;&atilde;o e modalidade operacional</li>
                            <li>Arquivos visuais complementares do processo de valida&ccedil;&atilde;o</li>
                        </ul>
                        <p>Ap&oacute;s o envio, a Fox Delivery inicia a confer&ecirc;ncia das informa&ccedil;&otilde;es e direciona a continuidade da ativa&ccedil;&atilde;o operacional.</p>
                    </div>
                    <div class="delivery-step-actions">
                        <button type="button" class="btn ghost" data-delivery-prev="4">Voltar</button>
                        <button type="submit" class="btn" data-track="delivery_registration_submit_click" data-track-component="registration_form" <?= $isDisabled ? 'disabled' : '' ?>>Enviar cadastro do entregador</button>
                    </div>
                </section>
            </div>
        </div>
    </form>
    <?php

    return (string) ob_get_clean();
}

function registration_render_alerts(array $errors, array $warnings = []): string
{
    if ($errors === [] && $warnings === []) {
        return '';
    }

    ob_start();
    foreach ($warnings as $warning):
        ?>
        <div class="registration-alert warning"><?= $warning ?></div>
        <?php
    endforeach;

    if ($errors !== []):
        ?>
        <div class="registration-alert error">
            <strong>Revise as informa&ccedil;&otilde;es abaixo:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    endif;

    return (string) ob_get_clean();
}

function registration_render_scripts(array $catalog): string
{
    $payload = [
        'modulesByZone' => $catalog['modules_by_zone'],
    ];

    ob_start();
    ?>
    <script>
        (function () {
            const registrationPayload = <?= json_encode($payload) ?>;
            const storeForms = document.querySelectorAll('[data-registration-form="store"]');
            const deliveryForms = document.querySelectorAll('[data-registration-form="delivery"]');
            const moduleOptions = (zoneId) => registrationPayload.modulesByZone[String(zoneId || '')] || [];

            const validateStoreStep = (section, report = false) => {
                if (!section) {
                    return true;
                }

                const minimumDeliveryField = section.querySelector('input[name="minimum_delivery_time"]');
                const maximumDeliveryField = section.querySelector('input[name="maximum_delivery_time"]');

                if (maximumDeliveryField) {
                    maximumDeliveryField.setCustomValidity('');
                }

                if (
                    minimumDeliveryField
                    && maximumDeliveryField
                    && minimumDeliveryField.value !== ''
                    && maximumDeliveryField.value !== ''
                    && Number(minimumDeliveryField.value) > Number(maximumDeliveryField.value)
                ) {
                    maximumDeliveryField.setCustomValidity('O prazo maximo deve ser maior ou igual ao prazo minimo.');
                }

                const handledRadioGroups = new Set();
                const fields = Array.from(section.querySelectorAll('input, select, textarea')).filter((field) => {
                    if (!field || field.disabled || field.type === 'hidden') {
                        return false;
                    }

                    const hiddenGroup = field.closest('.is-hidden');
                    if (hiddenGroup) {
                        return false;
                    }

                    return true;
                });

                for (const field of fields) {
                    if (field.type === 'radio') {
                        if (handledRadioGroups.has(field.name)) {
                            continue;
                        }

                        handledRadioGroups.add(field.name);
                        const group = Array.from(section.querySelectorAll(`input[type="radio"][name="${field.name}"]`)).filter((input) => !input.disabled && !input.closest('.is-hidden'));
                        const required = group.some((input) => input.required);
                        const checked = group.some((input) => input.checked);

                        if (required && !checked) {
                            if (report && group[0]) {
                                group[0].reportValidity();
                            }
                            return false;
                        }

                        continue;
                    }

                    if (!field.checkValidity()) {
                        if (report) {
                            field.reportValidity();
                        }
                        return false;
                    }
                }

                return true;
            };

            const deriveInitialStoreStep = (steps) => {
                for (let index = 0; index < steps.length; index += 1) {
                    if (!validateStoreStep(steps[index], false)) {
                        return index + 1;
                    }
                }

                return steps.length;
            };

            const updateStoreStepVisuals = (form, currentStep) => {
                const steps = Array.from(form.querySelectorAll('[data-store-step]'));
                const indicators = Array.from(form.querySelectorAll('[data-store-step-indicator]'));

                steps.forEach((step, index) => {
                    const stepNumber = index + 1;
                    step.classList.toggle('is-active', stepNumber === currentStep);
                });

                indicators.forEach((indicator, index) => {
                    const stepNumber = index + 1;
                    const relatedStep = steps[index];
                    const isComplete = relatedStep ? validateStoreStep(relatedStep, false) : false;
                    indicator.classList.toggle('is-active', stepNumber === currentStep);
                    indicator.classList.toggle('is-complete', stepNumber < currentStep || isComplete);
                });
            };

            const mountStoreWizard = (form) => {
                if (!form.matches('[data-store-wizard="true"]')) {
                    return;
                }

                const steps = Array.from(form.querySelectorAll('[data-store-step]'));
                if (!steps.length) {
                    return;
                }

                let currentStep = deriveInitialStoreStep(steps);

                const goToStep = (stepNumber) => {
                    currentStep = Math.max(1, Math.min(stepNumber, steps.length));
                    updateStoreStepVisuals(form, currentStep);

                    const activeStep = steps[currentStep - 1];
                    if (activeStep) {
                        activeStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                };

                form.querySelectorAll('[data-store-next]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const currentSection = steps[currentStep - 1];
                        if (!validateStoreStep(currentSection, true)) {
                            return;
                        }
                        goToStep(Number(button.dataset.storeNext || currentStep + 1));
                    });
                });

                form.querySelectorAll('[data-store-prev]').forEach((button) => {
                    button.addEventListener('click', () => {
                        goToStep(Number(button.dataset.storePrev || currentStep - 1));
                    });
                });

                form.querySelectorAll('[data-store-step-indicator]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const targetStep = Number(button.dataset.storeStepIndicator || currentStep);

                        if (targetStep > currentStep) {
                            const currentSection = steps[currentStep - 1];
                            if (!validateStoreStep(currentSection, true)) {
                                return;
                            }
                        }

                        goToStep(targetStep);
                    });
                });

                form.addEventListener('submit', (event) => {
                    for (let index = 0; index < steps.length; index += 1) {
                        if (!validateStoreStep(steps[index], false)) {
                            event.preventDefault();
                            goToStep(index + 1);
                            validateStoreStep(steps[index], true);
                            return;
                        }
                    }
                });

                form.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.addEventListener('change', () => updateStoreStepVisuals(form, currentStep));
                    field.addEventListener('input', () => updateStoreStepVisuals(form, currentStep));
                });

                updateStoreStepVisuals(form, currentStep);
            };

            const validateDeliveryStep = (section, report = false) => {
                if (!section) {
                    return true;
                }

                const handledRadioGroups = new Set();
                const fields = Array.from(section.querySelectorAll('input, select, textarea')).filter((field) => {
                    if (!field || field.disabled || field.type === 'hidden') {
                        return false;
                    }

                    const hiddenGroup = field.closest('.is-hidden');
                    if (hiddenGroup) {
                        return false;
                    }

                    return true;
                });

                for (const field of fields) {
                    if (field.type === 'radio') {
                        if (handledRadioGroups.has(field.name)) {
                            continue;
                        }

                        handledRadioGroups.add(field.name);
                        const group = Array.from(section.querySelectorAll(`input[type="radio"][name="${field.name}"]`)).filter((input) => !input.disabled && !input.closest('.is-hidden'));
                        const required = group.some((input) => input.required);
                        const checked = group.some((input) => input.checked);

                        if (required && !checked) {
                            if (report && group[0]) {
                                group[0].reportValidity();
                            }
                            return false;
                        }

                        continue;
                    }

                    if (!field.checkValidity()) {
                        if (report) {
                            field.reportValidity();
                        }
                        return false;
                    }
                }

                return true;
            };

            const deriveInitialDeliveryStep = (steps) => {
                for (let index = 0; index < steps.length; index += 1) {
                    if (!validateDeliveryStep(steps[index], false)) {
                        return index + 1;
                    }
                }

                return steps.length;
            };

            const updateDeliveryStepVisuals = (form, currentStep) => {
                const steps = Array.from(form.querySelectorAll('[data-delivery-step]'));
                const indicators = Array.from(form.querySelectorAll('[data-delivery-step-indicator]'));

                steps.forEach((step, index) => {
                    const stepNumber = index + 1;
                    step.classList.toggle('is-active', stepNumber === currentStep);
                });

                indicators.forEach((indicator, index) => {
                    const stepNumber = index + 1;
                    const relatedStep = steps[index];
                    const isComplete = relatedStep ? validateDeliveryStep(relatedStep, false) : false;
                    indicator.classList.toggle('is-active', stepNumber === currentStep);
                    indicator.classList.toggle('is-complete', stepNumber < currentStep || isComplete);
                });
            };

            const mountDeliveryWizard = (form) => {
                if (!form.matches('[data-delivery-wizard="true"]')) {
                    return;
                }

                const steps = Array.from(form.querySelectorAll('[data-delivery-step]'));
                if (!steps.length) {
                    return;
                }

                let currentStep = deriveInitialDeliveryStep(steps);

                const goToStep = (stepNumber) => {
                    currentStep = Math.max(1, Math.min(stepNumber, steps.length));
                    updateDeliveryStepVisuals(form, currentStep);

                    const activeStep = steps[currentStep - 1];
                    if (activeStep) {
                        activeStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                };

                form.querySelectorAll('[data-delivery-next]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const currentSection = steps[currentStep - 1];
                        if (!validateDeliveryStep(currentSection, true)) {
                            return;
                        }

                        goToStep(Number(button.dataset.deliveryNext || currentStep + 1));
                    });
                });

                form.querySelectorAll('[data-delivery-prev]').forEach((button) => {
                    button.addEventListener('click', () => {
                        goToStep(Number(button.dataset.deliveryPrev || currentStep - 1));
                    });
                });

                form.querySelectorAll('[data-delivery-step-indicator]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const targetStep = Number(button.dataset.deliveryStepIndicator || currentStep);

                        if (targetStep > currentStep) {
                            const currentSection = steps[currentStep - 1];
                            if (!validateDeliveryStep(currentSection, true)) {
                                return;
                            }
                        }

                        goToStep(targetStep);
                    });
                });

                form.addEventListener('submit', (event) => {
                    for (let index = 0; index < steps.length; index += 1) {
                        if (!validateDeliveryStep(steps[index], false)) {
                            event.preventDefault();
                            goToStep(index + 1);
                            validateDeliveryStep(steps[index], true);
                            return;
                        }
                    }
                });

                form.querySelectorAll('input, select, textarea').forEach((field) => {
                    field.addEventListener('change', () => updateDeliveryStepVisuals(form, currentStep));
                    field.addEventListener('input', () => updateDeliveryStepVisuals(form, currentStep));
                });

                updateDeliveryStepVisuals(form, currentStep);
            };

            const refreshStoreForm = (form) => {
                const zoneSelect = form.querySelector('[data-role="zone-select"]');
                const moduleSelect = form.querySelector('[data-role="module-select"]');
                const pickupGroup = form.querySelector('[data-role="pickup-zone-group"]');
                const pickupSelect = form.querySelector('[data-role="pickup-zone-select"]');
                const packageGroup = form.querySelector('[data-role="package-group"]');
                const planInputs = form.querySelectorAll('input[name="business_plan"]');

                if (!zoneSelect || !moduleSelect) {
                    return;
                }

                const previousValue = moduleSelect.dataset.selected || moduleSelect.value;
                const zoneModules = moduleOptions(zoneSelect.value);
                moduleSelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = zoneModules.length ? 'Selecione o m\u00f3dulo' : 'Nenhum m\u00f3dulo dispon\u00edvel para esta zona';
                moduleSelect.appendChild(placeholder);

                zoneModules.forEach((module) => {
                    const option = document.createElement('option');
                    option.value = module.id;
                    option.textContent = module.module_name;
                    option.dataset.moduleType = module.module_type;
                    if (String(module.id) === String(previousValue)) {
                        option.selected = true;
                    }
                    moduleSelect.appendChild(option);
                });

                moduleSelect.dataset.selected = moduleSelect.value;

                const selectedOption = moduleSelect.selectedOptions[0];
                const moduleType = selectedOption ? selectedOption.dataset.moduleType || '' : '';

                if (pickupGroup) {
                    pickupGroup.classList.toggle('is-hidden', moduleType !== 'rental');
                    if (pickupSelect) {
                        pickupSelect.required = moduleType === 'rental';
                    }
                    if (moduleType !== 'rental') {
                        pickupGroup.querySelectorAll('option').forEach((option) => {
                            option.selected = false;
                        });
                    }
                }

                if (packageGroup) {
                    const subscriptionSelected = Array.from(planInputs).some((input) => input.checked && input.value === 'subscription');
                    packageGroup.classList.toggle('is-hidden', !subscriptionSelected);
                    let firstVisibleRadio = null;

                    packageGroup.querySelectorAll('.package-card').forEach((card) => {
                        const packageType = card.dataset.moduleType || 'all';
                        const shouldShow = packageType === 'all' || packageType === moduleType || moduleType === '';
                        card.classList.toggle('is-hidden', !shouldShow);
                        const input = card.querySelector('input[type="radio"]');

                        if (!shouldShow && input) {
                            input.checked = false;
                            card.classList.remove('active');
                        }

                        if (shouldShow && subscriptionSelected && input && firstVisibleRadio === null) {
                            firstVisibleRadio = input;
                        }

                        if (input) {
                            input.required = false;
                        }
                    });

                    if (firstVisibleRadio) {
                        firstVisibleRadio.required = true;
                    }
                }
            };

            storeForms.forEach((form) => {
                const zoneSelect = form.querySelector('[data-role="zone-select"]');
                const moduleSelect = form.querySelector('[data-role="module-select"]');
                const planInputs = form.querySelectorAll('input[name="business_plan"]');

                if (zoneSelect) {
                    zoneSelect.addEventListener('change', () => {
                        const moduleField = form.querySelector('[data-role="module-select"]');
                        if (moduleField) {
                            moduleField.dataset.selected = '';
                        }
                        refreshStoreForm(form);
                    });
                }

                if (moduleSelect) {
                    moduleSelect.addEventListener('change', () => {
                        moduleSelect.dataset.selected = moduleSelect.value;
                        refreshStoreForm(form);
                    });
                }

                planInputs.forEach((input) => {
                    input.addEventListener('change', () => {
                        form.querySelectorAll('.plan-choice').forEach((card) => {
                            const radio = card.querySelector('input[type="radio"]');
                            card.classList.toggle('active', !!radio && radio.checked);
                        });
                        refreshStoreForm(form);
                    });
                });

                form.querySelectorAll('.package-card').forEach((card) => {
                    const input = card.querySelector('input[type="radio"]');
                    if (!input) {
                        return;
                    }

                    input.addEventListener('change', () => {
                        form.querySelectorAll('.package-card').forEach((item) => item.classList.remove('active'));
                        if (input.checked) {
                            card.classList.add('active');
                        }
                    });
                });

                refreshStoreForm(form);
                mountStoreWizard(form);
            });

            deliveryForms.forEach((form) => {
                mountDeliveryWizard(form);
            });
        })();
    </script>
    <?php

    return (string) ob_get_clean();
}

function registration_process_store(array $catalog, array $settings): array
{
    $state = registration_empty_state('store');
    $state['data'] = registration_collect_store_input();
    $errors = [];
    $data = $state['data'];
    $data['business_plan'] = registration_resolve_business_plan((string) $data['business_plan'], $settings);
    $state['data']['business_plan'] = $data['business_plan'];

    if (!$settings['store_enabled']) {
        $errors[] = 'O cadastro de loja est&aacute; temporariamente indispon&iacute;vel.';
    }

    foreach ([
        'f_name' => 'Informe o primeiro nome do respons&aacute;vel legal.',
        'store_name' => 'Informe o nome da loja.',
        'address' => 'Informe o endere&ccedil;o principal da loja.',
        'latitude' => 'Informe a latitude da opera&ccedil;&atilde;o.',
        'longitude' => 'Informe a longitude da opera&ccedil;&atilde;o.',
        'email' => 'Informe um e-mail v&aacute;lido.',
        'phone' => 'Informe um telefone para contato.',
        'minimum_delivery_time' => 'Informe o prazo m&iacute;nimo de entrega.',
        'maximum_delivery_time' => 'Informe o prazo m&aacute;ximo de entrega.',
        'delivery_time_type' => 'Informe a unidade do prazo de entrega.',
        'zone_id' => 'Selecione a zona de atendimento.',
        'module_id' => 'Selecione o m&oacute;dulo de neg&oacute;cio.',
        'password' => 'Defina a senha de acesso do respons&aacute;vel.',
    ] as $field => $message) {
        if ($data[$field] === '') {
            $errors[] = $message;
        }
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'O e-mail informado n&atilde;o &eacute; v&aacute;lido.';
    }

    if (!is_numeric($data['latitude']) || !is_numeric($data['longitude'])) {
        $errors[] = 'Latitude e longitude precisam ser num&eacute;ricas.';
    }

    if ((int) $data['minimum_delivery_time'] > (int) $data['maximum_delivery_time']) {
        $errors[] = 'O prazo m&aacute;ximo deve ser maior ou igual ao prazo m&iacute;nimo.';
    }

    if (strlen($data['password']) < 8) {
        $errors[] = 'A senha precisa ter no m&iacute;nimo 8 caracteres.';
    }

    if (!registration_has_uploaded_file($_FILES['logo'] ?? null)) {
        $errors[] = 'Envie a logo da loja para concluir o cadastro.';
    }

    if (!isset($catalog['zones_by_id'][$data['zone_id']])) {
        $errors[] = 'A zona selecionada n&atilde;o est&aacute; dispon&iacute;vel para cadastro.';
    }

    $module = $catalog['modules_by_id'][$data['module_id']] ?? null;
    $zoneModules = registration_modules_for_zone($catalog, $data['zone_id']);
    $zoneModuleIds = array_map(static fn (array $item): string => (string) $item['id'], $zoneModules);

    if ($module === null || !in_array((string) $data['module_id'], $zoneModuleIds, true)) {
        $errors[] = 'O m&oacute;dulo selecionado n&atilde;o pertence &agrave; zona informada.';
    }

    if (($module['module_type'] ?? '') === 'rental' && $data['pickup_zone_id'] === []) {
        $errors[] = 'Selecione ao menos uma zona de coleta para o m&oacute;dulo de locadora.';
    }

    if ($settings['subscription_enabled'] && $data['business_plan'] === 'subscription' && $data['package_id'] === '') {
        $errors[] = 'Escolha um pacote para o plano de assinatura.';
    }

    if ($data['package_id'] !== '' && !isset($catalog['packages_by_id'][$data['package_id']])) {
        $errors[] = 'O pacote selecionado n&atilde;o est&aacute; mais dispon&iacute;vel.';
    }

    if ($data['package_id'] !== '' && isset($catalog['packages_by_id'][$data['package_id']], $module['module_type'])) {
        $packageType = (string) $catalog['packages_by_id'][$data['package_id']]['module_type'];
        $moduleType = (string) $module['module_type'];

        if ($packageType !== 'all' && $packageType !== $moduleType) {
            $errors[] = 'O pacote selecionado n&atilde;o &eacute; compat&iacute;vel com o m&oacute;dulo escolhido.';
        }
    }

    if ($errors !== []) {
        $state['errors'] = array_values(array_unique($errors));
        return $state;
    }

    $businessPlan = registration_resolve_business_plan($data['business_plan'], $settings);
    $fields = [
        'f_name' => $data['f_name'],
        'l_name' => $data['l_name'],
        'latitude' => $data['latitude'],
        'longitude' => $data['longitude'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'minimum_delivery_time' => $data['minimum_delivery_time'],
        'maximum_delivery_time' => $data['maximum_delivery_time'],
        'delivery_time_type' => $data['delivery_time_type'],
        'password' => $data['password'],
        'zone_id' => $data['zone_id'],
        'module_id' => $data['module_id'],
        'tin' => $data['tin'],
        'tin_expire_date' => $data['tin_expire_date'] !== '' ? $data['tin_expire_date'] : 'null',
        'business_plan' => $businessPlan,
        'package_id' => $data['package_id'],
        'translations' => json_encode([
            ['locale' => 'pt-BR', 'key' => 'name', 'value' => $data['store_name']],
            ['locale' => 'pt-BR', 'key' => 'address', 'value' => $data['address']],
        ]),
    ];

    foreach ($data['pickup_zone_id'] as $index => $pickupZoneId) {
        $fields["pickup_zone_id[{$index}]"] = $pickupZoneId;
    }

    $response = registration_api_request(
        'api/v1/auth/vendor/register',
        $fields,
        [
            'logo' => $_FILES['logo'] ?? null,
            'cover_photo' => $_FILES['cover_photo'] ?? null,
            'tin_certificate_image' => $_FILES['tin_certificate_image'] ?? null,
        ]
    );

    if (!$response['ok']) {
        $state['errors'] = $response['messages'] !== [] ? $response['messages'] : ['N&atilde;o foi poss&iacute;vel enviar o cadastro da loja para o painel oficial.'];
        return $state;
    }

    if (($response['payload']['type'] ?? '') === 'business_model_fail') {
        $state['errors'] = ['Selecione um plano comercial v&aacute;lido para concluir o cadastro da loja.'];
        return $state;
    }

    $state['success'] = true;
    $state['response'] = $response['payload'];
    $state['message'] = ($response['payload']['type'] ?? '') === 'subscription'
        ? 'Cadastro da loja enviado com sucesso. O pacote selecionado foi vinculado ao pedido e a Fox Delivery seguir&aacute; com a an&aacute;lise pelo painel administrativo.'
        : 'Cadastro da loja enviado com sucesso. Os dados j&aacute; est&atilde;o vinculados ao painel administrativo da Fox Delivery.';

    return $state;
}

function registration_process_delivery(array $catalog, array $settings): array
{
    $state = registration_empty_state('delivery');
    $state['data'] = registration_collect_delivery_input();
    $errors = [];
    $data = $state['data'];

    if (!$settings['delivery_enabled']) {
        $errors[] = 'O cadastro de entregador est&aacute; temporariamente indispon&iacute;vel.';
    }

    foreach ([
        'f_name' => 'Informe o primeiro nome do entregador.',
        'identity_type' => 'Selecione o tipo de documento.',
        'identity_number' => 'Informe o n&uacute;mero do documento.',
        'email' => 'Informe um e-mail v&aacute;lido.',
        'phone' => 'Informe um telefone de contato.',
        'password' => 'Defina a senha de acesso.',
        'zone_id' => 'Selecione a zona de atendimento.',
        'vehicle_id' => 'Selecione o ve&iacute;culo.',
        'earning' => 'Selecione o modelo de remunera&ccedil;&atilde;o.',
    ] as $field => $message) {
        if ($data[$field] === '') {
            $errors[] = $message;
        }
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'O e-mail informado n&atilde;o &eacute; v&aacute;lido.';
    }

    if (strlen($data['password']) < 8) {
        $errors[] = 'A senha precisa ter no m&iacute;nimo 8 caracteres.';
    }

    if (!in_array($data['identity_type'], ['passport', 'driving_license', 'nid'], true)) {
        $errors[] = 'O tipo de documento informado n&atilde;o &eacute; aceito pelo painel.';
    }

    if (!isset($catalog['zones_by_id'][$data['zone_id']])) {
        $errors[] = 'A zona selecionada n&atilde;o est&aacute; dispon&iacute;vel.';
    }

    if (!isset($catalog['vehicles_by_id'][$data['vehicle_id']])) {
        $errors[] = 'O ve&iacute;culo selecionado n&atilde;o est&aacute; dispon&iacute;vel.';
    }

    if (!in_array($data['earning'], ['0', '1'], true)) {
        $errors[] = 'Selecione um modelo de remunera&ccedil;&atilde;o v&aacute;lido.';
    }

    if ($errors !== []) {
        $state['errors'] = array_values(array_unique($errors));
        return $state;
    }

    $response = registration_api_request(
        'api/v1/auth/delivery-man/store',
        [
            'f_name' => $data['f_name'],
            'l_name' => $data['l_name'],
            'identity_type' => $data['identity_type'],
            'identity_number' => $data['identity_number'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'zone_id' => $data['zone_id'],
            'vehicle_id' => $data['vehicle_id'],
            'earning' => $data['earning'],
            'referral_code' => $data['referral_code'],
        ],
        [
            'image' => $_FILES['image'] ?? null,
        ],
        [
            'identity_image' => $_FILES['identity_image'] ?? null,
        ]
    );

    if (!$response['ok']) {
        $state['errors'] = $response['messages'] !== [] ? $response['messages'] : ['N&atilde;o foi poss&iacute;vel enviar o cadastro do entregador para o painel oficial.'];
        return $state;
    }

    $state['success'] = true;
    $state['response'] = $response['payload'];
    $state['message'] = 'Cadastro do entregador enviado com sucesso. Os dados j&aacute; seguiram para valida&ccedil;&atilde;o no painel administrativo da Fox Delivery.';

    return $state;
}

function registration_api_request(string $path, array $fields, array $files = [], array $multiFiles = []): array
{
    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'payload' => [],
            'messages' => ['O servidor n&atilde;o possui suporte ao cURL para encaminhar o cadastro.'],
        ];
    }

    $postFields = [];
    foreach ($fields as $key => $value) {
        if ($value === null) {
            continue;
        }
        $postFields[$key] = (string) $value;
    }

    foreach ($files as $field => $file) {
        $curlFile = registration_build_curl_file($file);
        if ($curlFile !== null) {
            $postFields[$field] = $curlFile;
        }
    }

    foreach ($multiFiles as $field => $fileGroup) {
        foreach (registration_normalize_uploaded_files($fileGroup) as $index => $file) {
            $curlFile = registration_build_curl_file($file);
            if ($curlFile !== null) {
                $postFields["{$field}[{$index}]"] = $curlFile;
            }
        }
    }

    $handle = curl_init(sixammart_url($path));
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest',
        ],
    ]);

    $rawResponse = curl_exec($handle);

    if ($rawResponse === false) {
        $message = curl_error($handle);
        curl_close($handle);
        return [
            'ok' => false,
            'payload' => [],
            'messages' => [$message !== '' ? $message : 'Falha ao conectar com a API oficial de cadastro.'],
        ];
    }

    $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    curl_close($handle);
    $payload = json_decode($rawResponse, true);

    if (!is_array($payload)) {
        return [
            'ok' => false,
            'payload' => [],
            'messages' => ['A API oficial retornou uma resposta inesperada ao receber o cadastro.'],
        ];
    }

    $messages = registration_payload_messages($payload);
    $ok = $statusCode >= 200 && $statusCode < 300 && !isset($payload['errors']);

    return [
        'ok' => $ok,
        'payload' => $payload,
        'messages' => $messages,
    ];
}

function registration_payload_messages(array $payload): array
{
    $messages = [];

    if (isset($payload['errors']) && is_array($payload['errors'])) {
        foreach ($payload['errors'] as $error) {
            if (is_array($error) && isset($error['message'])) {
                $messages[] = (string) $error['message'];
            } elseif (is_string($error)) {
                $messages[] = $error;
            }
        }
    }

    if ($messages === [] && isset($payload['message']) && is_string($payload['message'])) {
        $messages[] = $payload['message'];
    }

    return $messages;
}

function registration_collect_store_input(): array
{
    return [
        'f_name' => registration_post_value('f_name'),
        'l_name' => registration_post_value('l_name'),
        'store_name' => registration_post_value('store_name'),
        'address' => registration_post_value('address'),
        'latitude' => registration_post_value('latitude'),
        'longitude' => registration_post_value('longitude'),
        'email' => registration_post_value('email'),
        'phone' => registration_post_value('phone'),
        'minimum_delivery_time' => registration_post_value('minimum_delivery_time'),
        'maximum_delivery_time' => registration_post_value('maximum_delivery_time'),
        'delivery_time_type' => registration_post_value('delivery_time_type', 'min'),
        'password' => registration_post_value('password'),
        'zone_id' => registration_post_value('zone_id'),
        'module_id' => registration_post_value('module_id'),
        'pickup_zone_id' => array_values(array_filter(array_map('strval', (array) ($_POST['pickup_zone_id'] ?? [])), static fn (string $value): bool => $value !== '')),
        'tin' => registration_post_value('tin'),
        'tin_expire_date' => registration_post_value('tin_expire_date'),
        'business_plan' => registration_post_value('business_plan', 'commission') === 'subscription' ? 'subscription' : 'commission',
        'package_id' => registration_post_value('package_id'),
    ];
}

function registration_collect_delivery_input(): array
{
    return [
        'f_name' => registration_post_value('f_name'),
        'l_name' => registration_post_value('l_name'),
        'identity_type' => registration_post_value('identity_type', 'nid'),
        'identity_number' => registration_post_value('identity_number'),
        'email' => registration_post_value('email'),
        'phone' => registration_post_value('phone'),
        'password' => registration_post_value('password'),
        'zone_id' => registration_post_value('zone_id'),
        'vehicle_id' => registration_post_value('vehicle_id'),
        'earning' => registration_post_value('earning', '1'),
        'referral_code' => registration_post_value('referral_code'),
    ];
}

function registration_post_value(string $field, string $default = ''): string
{
    return trim((string) ($_POST[$field] ?? $default));
}

function registration_empty_state(string $type): array
{
    if ($type === 'delivery') {
        return [
            'data' => [
                'f_name' => '',
                'l_name' => '',
                'identity_type' => 'nid',
                'identity_number' => '',
                'email' => '',
                'phone' => '',
                'password' => '',
                'zone_id' => '',
                'vehicle_id' => '',
                'earning' => '1',
                'referral_code' => '',
            ],
            'errors' => [],
            'success' => false,
            'message' => '',
            'response' => [],
        ];
    }

    return [
        'data' => [
            'f_name' => '',
            'l_name' => '',
            'store_name' => '',
            'address' => '',
            'latitude' => '',
            'longitude' => '',
            'email' => '',
            'phone' => '',
            'minimum_delivery_time' => '',
            'maximum_delivery_time' => '',
            'delivery_time_type' => 'min',
            'password' => '',
            'zone_id' => '',
            'module_id' => '',
            'pickup_zone_id' => [],
            'tin' => '',
            'tin_expire_date' => '',
            'business_plan' => 'commission',
            'package_id' => '',
        ],
        'errors' => [],
        'success' => false,
        'message' => '',
        'response' => [],
    ];
}

function registration_catalog(): array
{
    static $catalog = null;

    if ($catalog !== null) {
        return $catalog;
    }

    $zones = registration_fetch_all('SELECT id, name FROM zones WHERE status = 1 ORDER BY name');
    $vehicles = registration_fetch_all('SELECT id, type FROM d_m_vehicles WHERE status = 1 ORDER BY type');
    $moduleRows = registration_fetch_all(
        'SELECT DISTINCT mz.zone_id, m.id, m.module_name, m.module_type
         FROM module_zone mz
         INNER JOIN modules m ON m.id = mz.module_id
         WHERE m.status = 1 AND m.module_type <> :parcel
         ORDER BY m.module_name',
        ['parcel' => 'parcel']
    );
    $packageRows = registration_fetch_all(
        'SELECT id, package_name, price, validity, pos, mobile_app, chat, review, self_delivery, max_order, max_product, module_type
         FROM subscription_packages
         WHERE status = 1
         ORDER BY price, id'
    );

    $zoneList = array_map(static fn (array $zone): array => [
        'id' => (string) $zone['id'],
        'name' => (string) $zone['name'],
    ], $zones);
    $vehicleList = array_map(static fn (array $vehicle): array => [
        'id' => (string) $vehicle['id'],
        'type' => (string) $vehicle['type'],
    ], $vehicles);

    $modulesByZone = [];
    $modulesById = [];
    foreach ($moduleRows as $row) {
        $zoneId = (string) $row['zone_id'];
        $module = [
            'id' => (string) $row['id'],
            'module_name' => (string) $row['module_name'],
            'module_type' => (string) $row['module_type'],
        ];
        $modulesByZone[$zoneId][] = $module;
        $modulesById[(string) $row['id']] = $module;
    }

    $packages = array_map(static fn (array $package): array => [
        'id' => (string) $package['id'],
        'package_name' => (string) $package['package_name'],
        'price' => (float) $package['price'],
        'validity' => (string) $package['validity'],
        'pos' => (int) $package['pos'],
        'mobile_app' => (int) $package['mobile_app'],
        'chat' => (int) $package['chat'],
        'review' => (int) $package['review'],
        'self_delivery' => (int) $package['self_delivery'],
        'max_order' => (string) $package['max_order'],
        'max_product' => (string) $package['max_product'],
        'module_type' => (string) $package['module_type'],
    ], $packageRows);

    $catalog = [
        'zones' => $zoneList,
        'zones_by_id' => array_column($zoneList, null, 'id'),
        'vehicles' => $vehicleList,
        'vehicles_by_id' => array_column($vehicleList, null, 'id'),
        'modules_by_zone' => $modulesByZone,
        'modules_by_id' => $modulesById,
        'packages' => $packages,
        'packages_by_id' => array_column($packages, null, 'id'),
    ];

    return $catalog;
}

function registration_fetch_all(string $sql, array $params = []): array
{
    try {
        $statement = db()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    } catch (Throwable) {
        return [];
    }
}

function registration_settings(): array
{
    return [
        'store_enabled' => (string) get_business_setting('toggle_store_registration', '1') === '1',
        'delivery_enabled' => (string) get_business_setting('toggle_dm_registration', '1') === '1',
        'subscription_enabled' => (string) get_business_setting('subscription_business_model', '0') === '1',
        'commission_enabled' => (string) get_business_setting('commission_business_model', '1') !== '0',
        'currency_symbol' => (string) get_business_setting('currency_symbol', 'R$'),
        'currency_symbol_position' => (string) get_business_setting('currency_symbol_position', 'left'),
        'digit_after_decimal_point' => (int) get_business_setting('digit_after_decimal_point', 2),
    ];
}

function registration_sync_status(): array
{
    $appUrl = trim((string) env('APP_URL', ''));
    $apiBaseUrl = trim((string) env('SIXAMMART_BASE_URL', $appUrl));
    $dbHost = trim((string) env('DB_HOST', ''));
    $dbName = trim((string) env('DB_DATABASE', ''));

    $issues = [];
    if ($appUrl === '') {
        $issues[] = 'A URL principal do painel n&atilde;o est&aacute; definida no ambiente da landing.';
    }
    if ($apiBaseUrl === '') {
        $issues[] = 'A URL base das APIs oficiais n&atilde;o est&aacute; configurada.';
    }
    if ($dbHost === '' || $dbName === '') {
        $issues[] = 'As credenciais do banco principal n&atilde;o est&atilde;o completas para leitura dos campos oficiais.';
    }

    return [
        'app_url' => $appUrl,
        'api_base_url' => $apiBaseUrl,
        'db_ready' => $dbHost !== '' && $dbName !== '',
        'api_ready' => $apiBaseUrl !== '',
        'is_ready' => $issues === [],
        'issues' => $issues,
    ];
}

function registration_format_price(float $price, array $settings): string
{
    $formatted = number_format($price, max(0, (int) $settings['digit_after_decimal_point']), ',', '.');

    return ($settings['currency_symbol_position'] ?? 'left') === 'right'
        ? $formatted . ' ' . ($settings['currency_symbol'] ?? 'R$')
        : ($settings['currency_symbol'] ?? 'R$') . ' ' . $formatted;
}

function registration_package_features(array $package): array
{
    $features = [];

    if ((int) $package['mobile_app'] === 1) {
        $features[] = 'Aplicativo m&oacute;vel';
    }
    if ((int) $package['chat'] === 1) {
        $features[] = 'Chat com clientes';
    }
    if ((int) $package['review'] === 1) {
        $features[] = 'Avalia&ccedil;&otilde;es ativas';
    }
    if ((int) $package['pos'] === 1) {
        $features[] = 'Suporte a PDV';
    }
    if ((int) $package['self_delivery'] === 1) {
        $features[] = 'Autogest&atilde;o de entrega';
    }

    $maxOrders = (string) $package['max_order'];
    $maxProducts = (string) $package['max_product'];
    $features[] = $maxOrders === 'unlimited' ? 'Pedidos ilimitados' : $maxOrders . ' pedidos';
    $features[] = $maxProducts === 'unlimited' ? 'Uploads ilimitados' : $maxProducts . ' uploads';

    return $features;
}

function registration_should_hide_package(string $packageType, string $selectedModuleType): bool
{
    if ($selectedModuleType === '') {
        return false;
    }

    return $packageType !== 'all' && $packageType !== $selectedModuleType;
}

function registration_modules_for_zone(array $catalog, string $zoneId): array
{
    return $catalog['modules_by_zone'][$zoneId] ?? [];
}

function registration_module_type(array $catalog, string $moduleId): string
{
    return (string) ($catalog['modules_by_id'][$moduleId]['module_type'] ?? '');
}

function registration_resolve_business_plan(string $selectedPlan, array $settings): string
{
    if (!($settings['subscription_enabled'] ?? false)) {
        return 'commission';
    }

    if (!($settings['commission_enabled'] ?? false)) {
        return 'subscription';
    }

    return $selectedPlan === 'subscription' ? 'subscription' : 'commission';
}

function registration_normalize_type(string $type): string
{
    return $type === 'delivery' ? 'delivery' : 'store';
}

function registration_store_flash(string $type, string $message, array $response = []): void
{
    $_SESSION['fox_registration_flash'] = [
        'type' => registration_normalize_type($type),
        'message' => $message,
        'response' => $response,
    ];
}

function registration_consume_flash(): ?array
{
    if (!isset($_SESSION['fox_registration_flash']) || !is_array($_SESSION['fox_registration_flash'])) {
        return null;
    }

    $flash = $_SESSION['fox_registration_flash'];
    unset($_SESSION['fox_registration_flash']);

    return $flash;
}

function registration_redirect_after_submit(string $type): void
{
    $scriptName = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $path = (string) ($_SERVER['PHP_SELF'] ?? '');
    $location = $path;

    if ($scriptName === 'cadastro-parceiros.php') {
        $location .= '?tipo=' . registration_normalize_type($type);
    }

    header('Location: ' . $location);
    exit;
}

function registration_has_uploaded_file(mixed $file): bool
{
    return is_array($file)
        && isset($file['error'], $file['tmp_name'])
        && (int) $file['error'] === UPLOAD_ERR_OK
        && is_uploaded_file((string) $file['tmp_name']);
}

function registration_build_curl_file(mixed $file)
{
    if (!registration_has_uploaded_file($file)) {
        return null;
    }

    $tmpName = (string) $file['tmp_name'];
    $mimeType = (string) ($file['type'] ?? 'application/octet-stream');
    $originalName = (string) ($file['name'] ?? basename($tmpName));

    return new CURLFile($tmpName, $mimeType, $originalName);
}

function registration_normalize_uploaded_files(mixed $files): array
{
    if (!is_array($files) || !isset($files['name']) || !is_array($files['name'])) {
        return [];
    }

    $normalized = [];
    foreach ($files['name'] as $index => $name) {
        $normalized[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? 'application/octet-stream',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
    }

    return $normalized;
}

function data_get(mixed $target, string $key, mixed $default = null): mixed
{
    if (!is_array($target) || !array_key_exists($key, $target)) {
        return $default;
    }

    return $target[$key];
}
