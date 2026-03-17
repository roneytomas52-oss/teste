<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/registration_portal.php';

ob_start();
?>
<section class="fox-partner-page">
    <section class="fox-partner-hero">
        <div class="container fox-partner-topbar">
            <a class="fox-partner-brand" href="./index.php" aria-label="Fox Delivery">
                <img src="./assets/fox-brand.svg" alt="Fox Delivery">
            </a>

            <nav class="fox-partner-nav" aria-label="Navega&ccedil;&atilde;o do parceiro">
                <a href="./cadastro-parceiros.php">Menu parceiro</a>
                <a href="./index.php#blog">Blog</a>
                <a href="./sobre.php">Sobre n&oacute;s</a>
            </nav>

            <div class="fox-partner-actions">
                <a class="fox-partner-pill fox-partner-pill-soft" href="./index.php#apps">Baixar App</a>
                <a class="fox-partner-pill fox-partner-pill-light" href="#cadastro-opcoes">Cadastrar <span aria-hidden="true">&rsaquo;</span></a>
                <a class="fox-partner-pill" href="<?= e(sixammart_url('login')) ?>">Entrar</a>
            </div>
        </div>

        <div class="container fox-partner-hero-grid">
            <div class="fox-partner-copy">
                <h1>Cadastre-se<br>na Fox Delivery</h1>
                <p>Escolha como deseja participar da plataforma.</p>
            </div>

            <div class="fox-partner-scene" aria-hidden="true">
                <div class="fox-partner-scene-shell">
                    <div class="fox-partner-scene-badge">Fox Delivery Partners</div>

                    <div class="fox-partner-scene-main">
                        <span class="fox-partner-scene-kicker">Fluxo oficial</span>
                        <strong>Cadastro premium para lojas e entregadores</strong>
                        <p>Jornadas separadas, visual profissional e integra&ccedil;&atilde;o direta com a opera&ccedil;&atilde;o Fox Delivery.</p>
                    </div>

                    <div class="fox-partner-scene-route">
                        <span class="fox-partner-scene-dot fox-partner-scene-dot-start"></span>
                        <span class="fox-partner-scene-line"></span>
                        <span class="fox-partner-scene-dot fox-partner-scene-dot-mid"></span>
                        <span class="fox-partner-scene-line short"></span>
                        <span class="fox-partner-scene-dot fox-partner-scene-dot-end"></span>
                    </div>

                    <div class="fox-partner-scene-panels">
                        <article class="fox-partner-scene-panel">
                            <span class="fox-partner-scene-panel-label">Loja parceira</span>
                            <strong>Cat&aacute;logo, vendas e gest&atilde;o de pedidos</strong>
                            <p>Estrutura pensada para opera&ccedil;&atilde;o comercial e acompanhamento administrativo.</p>
                        </article>

                        <article class="fox-partner-scene-panel rider">
                            <span class="fox-partner-scene-panel-label">Entregador parceiro</span>
                            <strong>Credenciamento e acompanhamento operacional</strong>
                            <p>Jornada dedicada para cadastro, valida&ccedil;&atilde;o e entrada no fluxo da plataforma.</p>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="fox-partner-register" id="cadastro-opcoes">
        <div class="container fox-partner-register-shell">
            <header class="fox-partner-heading">
                <h2>Escolha como deseja se cadastrar</h2>
            </header>

            <div class="fox-partner-card-grid">
                <article class="fox-partner-card">
                    <div class="fox-partner-card-head">
                        <span class="fox-partner-card-tag">Loja parceira</span>
                        <h3>Cadastrar minha loja</h3>
                        <p>Cadastre seu restaurante, mercado ou farm&aacute;cia e comece a vender pela Fox Delivery.</p>
                    </div>

                    <ul class="fox-partner-checklist">
                        <li>Receba pedidos online</li>
                        <li>Alcance novos clientes</li>
                        <li>Gerencie pedidos pelo painel</li>
                    </ul>

                    <a class="fox-partner-cta" href="./cadastro-loja.php">Cadastrar loja</a>
                </article>

                <article class="fox-partner-card fox-partner-card-rider">
                    <div class="fox-partner-card-head">
                        <span class="fox-partner-card-tag rider">Entregador parceiro</span>
                        <h3>Quero ser entregador</h3>
                        <p>Trabalhe fazendo entregas com hor&aacute;rios flex&iacute;veis.</p>
                    </div>

                    <ul class="fox-partner-checklist">
                        <li>Ganhe por entrega</li>
                        <li>Trabalhe quando quiser</li>
                        <li>Use nosso app de entregador</li>
                    </ul>

                    <a class="fox-partner-cta fox-partner-cta-rider" href="./cadastro-entregador.php">Cadastrar entregador</a>
                </article>
            </div>

            <article class="fox-partner-integration">
                <div class="fox-partner-integration-icon" aria-hidden="true">
                    <span></span>
                </div>

                <div class="fox-partner-integration-copy">
                    <h3>Integra&ccedil;&atilde;o com o painel Fox Delivery</h3>
                    <p>Cadastros s&atilde;o sincronizados automaticamente com o painel administrativo.</p>
                </div>
            </article>
        </div>
    </section>
</section>
<?php

$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Parceiros';
$current = 'partners';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
