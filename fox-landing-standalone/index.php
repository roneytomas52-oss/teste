<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$download = get_data_setting('admin_landing_page', 'download_user_app_links', []);
$apple = is_array($download) ? (string) ($download['apple_store_url'] ?? '#') : '#';
$play = is_array($download) ? (string) ($download['playstore_url'] ?? '#') : '#';
$appleAttrs = $apple !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '';
$playAttrs = $play !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '';

ob_start();
?>
<section class="home-hub-hero">
    <div class="container home-hub-grid">
        <div class="home-hub-copy">
            <span class="eyebrow">Plataforma Fox Delivery</span>
            <h1>Pedidos, parceiros e entregas conectados em uma plataforma mais clara para operar.</h1>
            <p>A Fox Delivery organiza a entrada de cada publico em jornadas separadas, com leitura objetiva, mais contexto para a decisao e uma experiencia preparada para escalar a operacao.</p>
            <div class="home-hub-actions">
                <a class="btn" href="#categorias" data-track="home_categories_cta_click" data-track-component="hero">Explorar categorias</a>
                <a class="btn ghost" href="./cadastro-parceiros.php" data-track="home_registration_hub_click" data-track-component="hero">Iniciar cadastro</a>
            </div>
            <div class="home-hub-store-links" id="apps">
                <a class="store" href="<?= e($apple) ?>"<?= $appleAttrs ?> data-track="home_app_store_click" data-track-component="apps">App Store</a>
                <a class="store" href="<?= e($play) ?>"<?= $playAttrs ?> data-track="home_google_play_click" data-track-component="apps">Google Play</a>
            </div>
            <div class="home-hub-points">
                <span>Restaurantes, mercado e conveniencia</span>
                <span>Entrada comercial para parceiros</span>
                <span>Onboarding operacional para entregadores</span>
            </div>
        </div>

        <div class="home-hub-panel surface-card">
            <div class="home-hub-panel-head">
                <span class="flow-chip">Operacao Fox Delivery</span>
                <h3>Entradas organizadas para cada necessidade da plataforma</h3>
                <p>A jornada publica da Fox Delivery foi desenhada para reduzir ruido, dar mais clareza de leitura e conduzir o usuario para a proxima etapa correta.</p>
            </div>
            <div class="home-hub-signal-grid">
                <article class="home-hub-signal-card">
                    <small>Cliente final</small>
                    <strong>Comprar com praticidade</strong>
                    <p>Explore categorias e siga para a experiencia de pedido com mais clareza desde o inicio.</p>
                </article>
                <article class="home-hub-signal-card">
                    <small>Parceiros</small>
                    <strong>Vender na Fox Delivery</strong>
                    <p>Cadastre sua operacao em uma jornada comercial dedicada e mais organizada.</p>
                </article>
                <article class="home-hub-signal-card">
                    <small>Entregadores</small>
                    <strong>Entrar na operacao</strong>
                    <p>Acesse um fluxo proprio para perfil, documentos, modalidade e envio para analise.</p>
                </article>
            </div>
            <div class="home-hub-panel-footer">
                <strong>Mais contexto para decidir. Mais estrutura para operar.</strong>
                <span>Home, jornadas, ajuda e contato conectados em uma mesma linguagem visual e operacional.</span>
            </div>
        </div>
    </div>

    <div class="container home-hub-metrics">
        <article class="home-metric-card surface-card">
            <small>Cobertura de jornada</small>
            <strong>3 entradas principais</strong>
            <p>Cliente final, parceiros e entregadores com caminhos publicos separados.</p>
        </article>
        <article class="home-metric-card surface-card">
            <small>Leitura da plataforma</small>
            <strong>Categoria, cadastro e suporte</strong>
            <p>Uma arquitetura pensada para descoberta, decisao e continuidade operacional.</p>
        </article>
        <article class="home-metric-card surface-card">
            <small>Apoio institucional</small>
            <strong>Ajuda e contato integrados</strong>
            <p>Base inicial de orientacao para reduzir duvida e melhorar a entrada na Fox Delivery.</p>
        </article>
    </div>
</section>

<section class="container section home-section" id="categorias">
    <div class="home-section-shell">
        <div class="section-head">
            <span class="eyebrow">Categorias da plataforma</span>
            <h2>Servicos organizados para diferentes momentos da rotina urbana</h2>
            <p>A Fox Delivery distribui suas categorias em blocos claros para facilitar a leitura da plataforma e melhorar a escaneabilidade do usuario desde o primeiro acesso.</p>
        </div>
        <aside class="home-section-note surface-card">
            <span class="flow-chip">Leitura rapida</span>
            <strong>Categorias com funcao clara dentro da jornada</strong>
            <p>Em vez de concentrar tudo em uma unica vitrine, a plataforma sinaliza contextos de uso, recorrencia e expansao operacional.</p>
        </aside>
    </div>
    <div class="home-category-grid">
        <article class="home-category-card surface-card">
            <small>Pedido imediato</small>
            <strong>Restaurantes</strong>
            <p>Refeicoes e operacoes de alimentacao com experiencia de compra mais direta e objetiva.</p>
            <span class="home-category-foot">Consumo rapido e pedidos do dia</span>
        </article>
        <article class="home-category-card surface-card">
            <small>Compra do dia</small>
            <strong>Mercado</strong>
            <p>Abastecimento para casa, escritorio e rotina urbana com navegacao mais organizada.</p>
            <span class="home-category-foot">Compra recorrente e conveniencia</span>
        </article>
        <article class="home-category-card surface-card">
            <small>Essenciais</small>
            <strong>Farmacia</strong>
            <p>Produtos de farmacia e conveniencia em uma estrutura de acesso simples e escaneavel.</p>
            <span class="home-category-foot">Itens essenciais para diferentes horarios</span>
        </article>
        <article class="home-category-card surface-card">
            <small>Agilidade</small>
            <strong>Conveniencia</strong>
            <p>Itens de consumo rapido para diferentes contextos do dia a dia e horarios de urgencia.</p>
            <span class="home-category-foot">Operacoes de compra leve e imediata</span>
        </article>
        <article class="home-category-card surface-card">
            <small>Operacao local</small>
            <strong>Entregas urbanas</strong>
            <p>Fluxos conectados para pedidos, repasses, acompanhamento e continuidade da entrega.</p>
            <span class="home-category-foot">Leitura de ponta a ponta da operacao</span>
        </article>
        <article class="home-category-card surface-card">
            <small>Expansao</small>
            <strong>Novas operacoes</strong>
            <p>Estrutura preparada para receber novas categorias, parceiros e cobertura operacional.</p>
            <span class="home-category-foot">Escalabilidade para crescimento da plataforma</span>
        </article>
    </div>
</section>

<section class="section section-soft home-process-section">
    <div class="container">
        <div class="section-head center home-centered-head">
            <span class="eyebrow">Como funciona</span>
            <h2>Uma plataforma com jornadas claras do primeiro clique ao acompanhamento</h2>
            <p>A experiencia publica da Fox Delivery foi desenhada para organizar leitura, decisao e continuidade com menos atrito visual.</p>
        </div>
        <div class="home-process-grid">
            <article class="surface-card home-process-card">
                <span class="home-process-number">01</span>
                <strong>Entenda seu caminho</strong>
                <p>O usuario identifica rapidamente se deseja comprar, vender na plataforma ou iniciar a jornada como entregador.</p>
                <ul>
                    <li>Home como hub de decisao</li>
                    <li>Mais contexto antes da proxima etapa</li>
                </ul>
            </article>
            <article class="surface-card home-process-card">
                <span class="home-process-number">02</span>
                <strong>Siga para a jornada correta</strong>
                <p>Cada fluxo leva para uma tela propria, com copy, estrutura e formulario adequados ao tipo de usuario.</p>
                <ul>
                    <li>Parceiros com entrada comercial</li>
                    <li>Entregadores com onboarding operacional</li>
                </ul>
            </article>
            <article class="surface-card home-process-card">
                <span class="home-process-number">03</span>
                <strong>Conte com apoio da plataforma</strong>
                <p>Ajuda, contato e leitura institucional complementam a jornada e reforcam a confianca na operacao da Fox Delivery.</p>
                <ul>
                    <li>Base de ajuda integrada</li>
                    <li>Contato para continuidade e suporte</li>
                </ul>
            </article>
        </div>
    </div>
</section>

<section class="container section home-section">
    <div class="home-section-shell">
        <div class="section-head">
            <span class="eyebrow">Entradas da plataforma</span>
            <h2>Fluxos dedicados para parceiros e entregadores</h2>
            <p>A Fox Delivery organiza a entrada operacional em jornadas distintas para evitar mistura de mensagens e tornar a decisao mais clara desde o inicio.</p>
        </div>
        <aside class="home-section-note surface-card">
            <span class="flow-chip">Decisao orientada</span>
            <strong>Escolha o fluxo certo antes do formulario principal</strong>
            <p>Parceiros e entregadores nao entram pela mesma estrutura. Cada publico segue para sua propria experiencia.</p>
        </aside>
    </div>
    <div class="home-journey-grid">
        <a class="home-journey-card surface-card" href="./cadastro-parceiros.php" data-track="home_partner_journey_click" data-track-component="journey_card">
            <small>Fox Delivery Parceiros</small>
            <h3>Quero vender na Fox Delivery</h3>
            <p>Entre em uma jornada preparada para cadastro de loja, operacao, documentos e acompanhamento da aprovacao.</p>
            <ul>
                <li>Entrada comercial para lojas parceiras</li>
                <li>Leitura organizada de etapas e analise</li>
                <li>Contexto proprio antes do cadastro completo</li>
            </ul>
            <span class="btn">Ir para parceiros</span>
        </a>
        <a class="home-journey-card delivery surface-card" href="./cadastro-entregador.php" data-track="home_delivery_journey_click" data-track-component="journey_card">
            <small>Fox Delivery Entregadores</small>
            <h3>Quero entregar com a Fox Delivery</h3>
            <p>Acesse um fluxo proprio para onboarding do entregador, com requisitos, validacao e organizacao operacional.</p>
            <ul>
                <li>Cadastro por etapa com mais clareza</li>
                <li>Perfil, documentos e modalidade na mesma jornada</li>
                <li>Entrada pensada para continuidade operacional</li>
            </ul>
            <span class="btn">Ir para entregadores</span>
        </a>
    </div>
</section>

<section class="section home-faq-section">
    <div class="container">
        <div class="section-head center home-centered-head">
            <span class="eyebrow">Perguntas frequentes</span>
            <h2>Informacoes essenciais para quem esta entrando na Fox Delivery</h2>
            <p>Uma base curta para orientar a proxima acao do usuario e reduzir duvidas iniciais antes do contato com a equipe.</p>
        </div>
        <div class="home-faq-layout">
            <div class="home-faq-grid">
                <details class="home-faq-item surface-card" open>
                    <summary>O que encontro na Fox Delivery?</summary>
                    <p>A plataforma organiza pedidos de restaurantes, mercado, farmacia, conveniencia e outras operacoes urbanas em uma experiencia unica.</p>
                </details>
                <details class="home-faq-item surface-card">
                    <summary>Como faco para cadastrar minha loja?</summary>
                    <p>O caminho ideal e iniciar por <a href="./cadastro-parceiros.php">Parceiros</a>, onde a Fox Delivery direciona o fluxo correto para cadastro da sua operacao.</p>
                </details>
                <details class="home-faq-item surface-card">
                    <summary>Como comeco a entregar com a Fox Delivery?</summary>
                    <p>A jornada de entregadores fica em uma pagina propria, com orientacao sobre requisitos, etapas e envio de informacoes.</p>
                </details>
                <details class="home-faq-item surface-card">
                    <summary>Onde posso falar com a equipe da Fox Delivery?</summary>
                    <p>Comece pela <a href="./ajuda.php">Central de ajuda</a> e, se precisar, siga para a pagina de <a href="./contato.php#duvidas">Contato</a> para falar com o time da plataforma.</p>
                </details>
            </div>
            <aside class="home-faq-aside surface-card">
                <span class="flow-chip">Suporte Fox Delivery</span>
                <h3>Ajuda, contato e leitura institucional conectados</h3>
                <p>Se quiser entender melhor a plataforma antes de seguir para cadastro ou operacao, a Fox Delivery centraliza apoio em paginas dedicadas.</p>
                <div class="partner-final-actions">
                    <a class="btn" href="./ajuda.php" data-track="home_help_click" data-track-component="faq_callout">Central de ajuda</a>
                    <a class="btn ghost" href="./como-funciona.php" data-track="home_how_click" data-track-component="faq_callout">Como funciona</a>
                </div>
            </aside>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Inicio';
$current = 'home';
require __DIR__ . '/includes/layout.php';
