<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/registration_portal.php';

$syncStatus = registration_sync_status();

ob_start();
?>
<section class="partner-poster">
    <div class="container partner-topbar">
        <a class="partner-brand" href="./index.php" aria-label="Fox Delivery">
            <img src="./assets/fox-brand.svg" alt="Fox Delivery">
        </a>

        <nav class="partner-nav" aria-label="Navega&ccedil;&atilde;o do parceiro">
            <a href="./cadastro-parceiros.php">Menu parceiro</a>
            <a href="./index.php#blog">Blog</a>
            <a href="./sobre.php">Sobre n&oacute;s</a>
        </nav>

        <div class="partner-actions">
            <a class="partner-pill secondary" href="./index.php#apps">Baixar App</a>
            <a class="partner-pill ghost" href="#cadastro-opcoes">Cadastrar <span aria-hidden="true">&rsaquo;</span></a>
            <a class="partner-pill" href="<?= e(sixammart_url('login')) ?>">Entrar</a>
        </div>
    </div>

    <div class="container partner-hero-grid">
        <div class="partner-hero-copy">
            <h1>Cadastre-se<br>na Fox Delivery</h1>
            <p>Escolha como deseja participar da plataforma.</p>
        </div>

        <div class="partner-hero-figure">
            <img src="./assets/fox-hero-rider.svg" alt="Raposa da Fox Delivery em uma moto de entregas">
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
                    <img class="partner-option-art" src="./assets/partner-storefront.svg" alt="Loja parceira">
                    <div class="partner-option-copy">
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
                    <img class="partner-option-art delivery" src="./assets/partner-rider-card.svg" alt="Entregador parceiro">
                    <div class="partner-option-copy">
                        <h3>Quero ser entregador</h3>
                        <p>Trabalhe fazendo entregas com hor&aacute;rios flex&iacute;veis.</p>
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
            <div class="partner-sync-icon" aria-hidden="true"><span></span></div>
            <div class="partner-sync-copy">
                <h3>Integra&ccedil;&atilde;o com o painel Fox Delivery</h3>
                <p>Cadastros s&atilde;o sincronizados automaticamente com o painel administrativo.</p>
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
