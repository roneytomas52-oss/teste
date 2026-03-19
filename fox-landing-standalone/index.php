<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$download = get_data_setting('admin_landing_page', 'download_user_app_links', []);
$apple = is_array($download) ? (string)($download['apple_store_url'] ?? '#') : '#';
$play = is_array($download) ? (string)($download['playstore_url'] ?? '#') : '#';
$appleAttrs = $apple !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '';
$playAttrs = $play !== '#' ? ' target="_blank" rel="noopener noreferrer"' : '';

ob_start();
?>
<section class="home-hub-hero">
    <div class="container home-hub-grid">
        <div class="home-hub-copy">
            <span class="eyebrow">Plataforma Fox Delivery</span>
            <h1>A Fox Delivery conecta pedidos, parceiros e entregadores em uma experiencia organizada.</h1>
            <p>Uma plataforma preparada para rotina urbana, com jornadas separadas para comprar, vender e entregar com mais clareza operacional.</p>
            <div class="cta-row">
                <a class="btn" href="#categorias" data-track="home_categories_cta_click" data-track-component="hero">Explorar categorias</a>
                <a class="btn ghost" href="./cadastro-parceiros.php" data-track="home_registration_hub_click" data-track-component="hero">Iniciar cadastro</a>
            </div>
            <div class="cta-row" id="apps">
                <a class="store" href="<?= e($apple) ?>"<?= $appleAttrs ?> data-track="home_app_store_click" data-track-component="apps">App Store</a>
                <a class="store" href="<?= e($play) ?>"<?= $playAttrs ?> data-track="home_google_play_click" data-track-component="apps">Google Play</a>
            </div>
            <div class="home-hub-points">
                <span>Restaurantes e mercado</span>
                <span>Operacao para parceiros</span>
                <span>Jornada para entregadores</span>
            </div>
        </div>
        <div class="home-hub-panel surface-card">
            <span class="flow-chip">Jornadas Fox Delivery</span>
            <h3>Escolha a entrada ideal para sua necessidade</h3>
            <p>Cliente final, parceiro e entregador seguem fluxos claros, com orientacao adequada para cada etapa da plataforma.</p>
            <div class="home-hub-stack">
                <div class="home-hub-item">
                    <strong>Comprar com praticidade</strong>
                    <span>Peca refeicoes, mercado, farmacia e conveniencia em uma unica experiencia.</span>
                </div>
                <div class="home-hub-item">
                    <strong>Vender na Fox Delivery</strong>
                    <span>Cadastre sua loja com uma jornada comercial e operacional organizada.</span>
                </div>
                <div class="home-hub-item">
                    <strong>Entregar com a Fox Delivery</strong>
                    <span>Entre para a operacao com um fluxo proprio para cadastro e validacao.</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container section home-section" id="categorias">
    <div class="section-head">
        <span class="eyebrow">Categorias da plataforma</span>
        <h2>Servicos e categorias para diferentes momentos da rotina</h2>
        <p>A estrutura da Fox Delivery foi desenhada para atender pedidos do dia a dia com organizacao, velocidade de acesso e clareza para o usuario final.</p>
    </div>
    <div class="home-category-grid">
        <article class="home-category-card surface-card">
            <small>Pedido imediato</small>
            <strong>Restaurantes</strong>
            <p>Refeicoes e operacoes de alimentacao com experiencia de compra clara e objetiva.</p>
        </article>
        <article class="home-category-card surface-card">
            <small>Compra do dia</small>
            <strong>Mercado</strong>
            <p>Abastecimento rapido para casa, escritorio e rotina urbana com praticidade.</p>
        </article>
        <article class="home-category-card surface-card">
            <small>Essenciais</small>
            <strong>Farmacia</strong>
            <p>Produtos de farmacia e conveniencia com navegacao organizada e acesso simplificado.</p>
        </article>
        <article class="home-category-card surface-card">
            <small>Agilidade</small>
            <strong>Conveniencia</strong>
            <p>Itens de consumo rapido para diferentes horarios, contextos e necessidades do dia.</p>
        </article>
        <article class="home-category-card surface-card">
            <small>Operacao local</small>
            <strong>Entregas urbanas</strong>
            <p>Fluxos conectados para pedidos, repasses e acompanhamento da entrega.</p>
        </article>
        <article class="home-category-card surface-card">
            <small>Expansao</small>
            <strong>Novas operacoes</strong>
            <p>Estrutura preparada para receber novos parceiros, categorias e cobertura operacional.</p>
        </article>
    </div>
</section>

<section class="section section-soft home-process-section">
    <div class="container">
        <div class="section-head center home-centered-head">
            <span class="eyebrow">Como funciona</span>
            <h2>Uma plataforma com jornadas claras do inicio ao acompanhamento</h2>
            <p>A experiencia publica da Fox Delivery precisa ser simples para o usuario final e organizada para quem opera a plataforma.</p>
        </div>
        <div class="home-process-grid">
            <article class="surface-card home-process-card">
                <span class="home-process-number">01</span>
                <strong>Escolha sua jornada</strong>
                <p>O usuario entende rapidamente se quer pedir, vender na plataforma ou iniciar a jornada como entregador.</p>
            </article>
            <article class="surface-card home-process-card">
                <span class="home-process-number">02</span>
                <strong>Ative sua operacao</strong>
                <p>Cada fluxo segue para uma tela dedicada, com contexto, copy e acao apropriados para aquela necessidade.</p>
            </article>
            <article class="surface-card home-process-card">
                <span class="home-process-number">03</span>
                <strong>Acompanhe com mais clareza</strong>
                <p>A plataforma organiza a experiencia de ponta a ponta, com acesso a painel, suporte e proximos passos.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section home-section">
    <div class="section-head">
        <span class="eyebrow">Entradas da plataforma</span>
        <h2>Fluxos dedicados para parceiros e entregadores</h2>
        <p>Em vez de misturar tudo na mesma tela, a Fox Delivery organiza a entrada de cada publico em jornadas separadas e objetivas.</p>
    </div>
    <div class="home-journey-grid">
        <a class="home-journey-card surface-card" href="./cadastro-parceiros.php" data-track="home_partner_journey_click" data-track-component="journey_card">
            <small>Fox Delivery Parceiros</small>
            <h3>Quero vender na Fox Delivery</h3>
            <p>Entre em uma jornada comercial preparada para cadastro de loja, operacao, documentos e acompanhamento da aprovacao.</p>
            <ul>
                <li>Cadastro comercial da operacao</li>
                <li>Entrada dedicada para loja parceira</li>
                <li>Orientacao clara sobre proximos passos</li>
            </ul>
            <span class="btn">Ir para parceiros</span>
        </a>
        <a class="home-journey-card delivery surface-card" href="./cadastro-entregador.php" data-track="home_delivery_journey_click" data-track-component="journey_card">
            <small>Fox Delivery Entregadores</small>
            <h3>Quero entregar com a Fox Delivery</h3>
            <p>Acesse um fluxo proprio para onboarding do entregador, com requisitos, validacao e organizacao operacional.</p>
            <ul>
                <li>Cadastro proprio para entregadores</li>
                <li>Jornada orientada por etapa</li>
                <li>Entrada clara para documentacao e envio</li>
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
            <p>Uma base curta e objetiva para reduzir duvidas iniciais e orientar a proxima acao do usuario.</p>
        </div>
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
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Inicio';
$current = 'home';
require __DIR__ . '/includes/layout.php';
