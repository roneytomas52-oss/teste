<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/registration_portal.php';

$syncStatus = registration_sync_status();

ob_start();
?>
<section class="partner-poster">
    <div class="container partner-topbar">
        <a class="partner-brand" href="./index.php" aria-label="Fox Delivery">
            <img src="./assets/fox-mascot.svg" alt="Fox Delivery">
            <span>FOX <small>Delivery</small></span>
        </a>

        <nav class="partner-nav" aria-label="Navega&ccedil;&atilde;o do parceiro">
            <a href="./cadastro-parceiros.php">Menu parceiro</a>
            <a href="./index.php#blog">Blog</a>
            <a href="./sobre.php">Sobre n&oacute;s</a>
        </nav>

        <div class="partner-actions">
            <a class="partner-pill secondary" href="./index.php#apps">Baixar App</a>
            <a class="partner-pill ghost" href="#cadastro-opcoes">Cadastrar</a>
            <a class="partner-pill" href="<?= e(sixammart_url('login')) ?>">Entrar</a>
        </div>
    </div>

    <div class="container partner-hero-grid">
        <div class="partner-hero-copy">
            <span class="partner-hero-eyebrow">Fox Delivery parceiros</span>
            <h1>Cadastre-se<br>na Fox Delivery</h1>
            <p>Escolha como deseja participar da plataforma.</p>

            <div class="partner-hero-points" aria-label="Destaques do cadastro">
                <span>Fluxos separados para loja e entregador</span>
                <span>Painel sincronizado com a opera&ccedil;&atilde;o</span>
            </div>
        </div>

        <div class="partner-hero-figure">
            <div class="partner-hero-badge">Integra&ccedil;&atilde;o direta com o painel</div>
            <img src="./assets/fox-mascot.svg" alt="Mascote Fox Delivery">
        </div>
    </div>
</section>

<section class="partner-choice-surface" id="cadastro-opcoes">
    <div class="container partner-choice-shell">
        <div class="partner-choice-heading">
            <h2>Escolha como deseja se cadastrar</h2>
        </div>

        <?php if (!$syncStatus['is_ready']): ?>
            <?= registration_render_alerts([], $syncStatus['issues']) ?>
        <?php endif; ?>

        <div class="partner-choice-grid">
            <article class="partner-option-card">
                <div class="partner-option-header">
                    <div class="partner-option-icon store" aria-hidden="true">
                        <svg viewBox="0 0 64 64" role="presentation">
                            <rect x="10" y="22" width="44" height="28" rx="4"></rect>
                            <path d="M14 14h36l4 10H10z"></path>
                            <path d="M20 30h10v20H20z"></path>
                            <path d="M36 30h12v10H36z"></path>
                        </svg>
                    </div>
                    <div class="partner-option-copy">
                        <span class="partner-option-badge">Loja parceira</span>
                        <h3>Cadastrar minha loja</h3>
                        <p>Cadastre seu restaurante, mercado ou farm&aacute;cia e comece a vender pela Fox Delivery.</p>
                    </div>
                </div>

                <ul class="partner-benefits">
                    <li>Receba pedidos online</li>
                    <li>Alcance novos clientes</li>
                    <li>Gerencie pedidos pelo painel</li>
                </ul>

                <a class="partner-primary-cta" href="./cadastro-loja.php">Cadastrar loja</a>
            </article>

            <article class="partner-option-card delivery">
                <div class="partner-option-header">
                    <div class="partner-option-icon delivery" aria-hidden="true">
                        <svg viewBox="0 0 64 64" role="presentation">
                            <circle cx="20" cy="46" r="8"></circle>
                            <circle cx="48" cy="46" r="8"></circle>
                            <path d="M21 46l10-16h10l8 8"></path>
                            <path d="M28 28h12l6 8H32z"></path>
                            <rect x="12" y="18" width="18" height="12" rx="3"></rect>
                        </svg>
                    </div>
                    <div class="partner-option-copy">
                        <span class="partner-option-badge delivery">Entregador parceiro</span>
                        <h3>Quero ser entregador</h3>
                        <p>Trabalhe fazendo entregas com hor&aacute;rios flex&iacute;veis e credenciamento direto pela Fox Delivery.</p>
                    </div>
                </div>

                <ul class="partner-benefits">
                    <li>Ganhe por entrega</li>
                    <li>Trabalhe quando quiser</li>
                    <li>Use nosso app de entregador</li>
                </ul>

                <a class="partner-primary-cta delivery" href="./cadastro-entregador.php">Cadastrar entregador</a>
            </article>
        </div>

        <div class="partner-sync-card">
            <div class="partner-sync-icon" aria-hidden="true">&#10003;</div>
            <div class="partner-sync-copy">
                <h3>Integra&ccedil;&atilde;o com o painel Fox Delivery</h3>
                <p>Cadastros s&atilde;o sincronizados automaticamente com o painel administrativo.</p>
            </div>
            <div class="partner-sync-status">
                <span class="<?= $syncStatus['db_ready'] ? 'ok' : 'warn' ?>">Banco <?= $syncStatus['db_ready'] ? 'conectado' : 'revisar' ?></span>
                <span class="<?= $syncStatus['api_ready'] ? 'ok' : 'warn' ?>">API <?= $syncStatus['api_ready'] ? 'configurada' : 'revisar' ?></span>
            </div>
        </div>
    </div>
</section>
<?php

$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Parceiros';
$current = 'partners';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
