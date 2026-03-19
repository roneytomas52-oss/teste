<?php

declare(strict_types=1);

ob_start();
?>
<section class="page-hero">
    <div class="container section-head">
        <span class="eyebrow">Como funciona</span>
        <h1>Como a Fox Delivery organiza as jornadas da plataforma</h1>
        <p>A estrutura publica da Fox Delivery separa cliente final, parceiro e entregador em entradas diferentes para dar mais contexto, orientar a proxima etapa e reduzir atrito na operacao.</p>
    </div>
</section>

<section class="container section">
    <div class="section-head">
        <span class="eyebrow">Jornadas da plataforma</span>
        <h2>Tres caminhos principais dentro da experiencia Fox Delivery</h2>
        <p>Cada publico encontra uma leitura adequada do que precisa fazer, sem depender de uma pagina unica tentando explicar tudo ao mesmo tempo.</p>
    </div>
    <div class="info-card-grid">
        <article class="info-card surface-card">
            <strong>Cliente final</strong>
            <p>Entra pela home para entender categorias, explorar a plataforma e seguir para a jornada de compra com mais rapidez.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Parceiro</strong>
            <p>Passa por uma pagina de decisao e segue para um cadastro comercial e operacional proprio da loja parceira.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Entregador</strong>
            <p>Segue para um onboarding dedicado com perfil, identificacao, operacao, documentos e envio para analise.</p>
        </article>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head centered-head">
            <span class="eyebrow">Leitura operacional</span>
            <h2>O fluxo da Fox Delivery foi desenhado para deixar a plataforma mais clara</h2>
            <p>Em vez de concentrar tudo em um unico formulario ou hero, a plataforma distribui informacao, decisao e proxima acao por etapa.</p>
        </div>
        <div class="operation-flow-grid">
            <article class="operation-step surface-card">
                <span>01</span>
                <strong>Escolha da jornada</strong>
                <p>O usuario entende se deseja comprar, vender na plataforma ou entrar como entregador.</p>
            </article>
            <article class="operation-step surface-card">
                <span>02</span>
                <strong>Entrada adequada</strong>
                <p>Cada jornada direciona para a pagina correta, com copy, FAQ e formulario compativeis com o publico.</p>
            </article>
            <article class="operation-step surface-card">
                <span>03</span>
                <strong>Analise e continuidade</strong>
                <p>Depois do envio, a Fox Delivery segue com conferencia, retorno e orientacao da proxima etapa operacional.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section split">
    <div class="panel">
        <h3>Suporte e orientacao</h3>
        <p>A plataforma usa FAQ, pagina de ajuda, pagina de contato e jornadas explicadas para reduzir duvidas recorrentes e dar mais confianca a quem esta entrando no ecossistema da Fox Delivery.</p>
    </div>
    <div class="panel">
        <h3>Cadastro e ativacao</h3>
        <p>Lojas e entregadores nao entram pelo mesmo caminho. A Fox Delivery organiza esse processo para separar contexto comercial, contexto operacional e validacao documental.</p>
    </div>
</section>

<section class="container section">
    <div class="support-callout surface-card">
        <div>
            <span class="eyebrow">Acesse as jornadas</span>
            <h2>Siga para a entrada certa dentro da Fox Delivery</h2>
            <p>Use a landing de parceiros para decidir entre loja e entregador, ou va direto para a central de ajuda se ainda quiser revisar as orientacoes da plataforma.</p>
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
