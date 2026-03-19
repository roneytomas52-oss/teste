<?php

declare(strict_types=1);

ob_start();
?>
<section class="page-hero">
    <div class="container section-head">
        <span class="eyebrow">Como funciona</span>
        <h1>Como a Fox Delivery organiza as jornadas da plataforma</h1>
        <p>A estrutura pública da Fox Delivery separa cliente final, parceiro e entregador em entradas diferentes para dar mais contexto, orientar a próxima etapa e reduzir atrito na operação.</p>
    </div>
</section>

<section class="container section">
    <div class="section-head">
        <span class="eyebrow">Jornadas da plataforma</span>
        <h2>Três caminhos principais dentro da experiência Fox Delivery</h2>
        <p>Cada público encontra uma leitura adequada do que precisa fazer, sem depender de uma página única tentando explicar tudo ao mesmo tempo.</p>
    </div>
    <div class="info-card-grid">
        <article class="info-card surface-card">
            <strong>Cliente final</strong>
            <p>Entra pela home para entender categorias, explorar a plataforma e seguir para a jornada de compra com mais rapidez.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Parceiro</strong>
            <p>Passa por uma página de decisão e segue para um cadastro comercial e operacional próprio da loja parceira.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Entregador</strong>
            <p>Segue para um onboarding dedicado, com perfil, identificação, operação, documentos e envio para análise.</p>
        </article>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head centered-head">
            <span class="eyebrow">Leitura operacional</span>
            <h2>O fluxo da Fox Delivery foi desenhado para deixar a plataforma mais clara</h2>
            <p>Em vez de concentrar tudo em um único formulário ou hero, a plataforma distribui informação, decisão e próxima ação por etapa.</p>
        </div>
        <div class="operation-flow-grid">
            <article class="operation-step surface-card">
                <span>01</span>
                <strong>Escolha da jornada</strong>
                <p>O usuário entende se deseja comprar, vender na plataforma ou entrar como entregador.</p>
            </article>
            <article class="operation-step surface-card">
                <span>02</span>
                <strong>Entrada adequada</strong>
                <p>Cada jornada direciona para a página correta, com copy, FAQ e formulário compatíveis com o público.</p>
            </article>
            <article class="operation-step surface-card">
                <span>03</span>
                <strong>Análise e continuidade</strong>
                <p>Depois do envio, a Fox Delivery segue com conferência, retorno e orientação da próxima etapa operacional.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section split">
    <div class="panel">
        <h3>Suporte e orientação</h3>
        <p>A plataforma usa FAQ, página de ajuda, página de contato e jornadas explicadas para reduzir dúvidas recorrentes e dar mais confiança a quem está entrando no ecossistema da Fox Delivery.</p>
    </div>
    <div class="panel">
        <h3>Cadastro e ativação</h3>
        <p>Lojas e entregadores não entram pelo mesmo caminho. A Fox Delivery organiza esse processo para separar contexto comercial, contexto operacional e validação documental.</p>
    </div>
</section>

<section class="container section">
    <div class="support-callout surface-card">
        <div>
            <span class="eyebrow">Acesse as jornadas</span>
            <h2>Siga para a entrada certa dentro da Fox Delivery</h2>
            <p>Use a landing de parceiros para decidir entre loja e entregador, ou vá direto para a central de ajuda se ainda quiser revisar as orientações da plataforma.</p>
        </div>
        <div class="partner-final-actions">
            <a class="btn" href="./cadastro-parceiros.php" data-track="how_partners_click" data-track-component="support_callout">Ir para parceiros</a>
            <a class="btn ghost" href="./ajuda.php" data-track="how_help_click" data-track-component="support_callout">Ir para ajuda</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Como funciona';
$current = 'how';
require __DIR__ . '/includes/layout.php';
