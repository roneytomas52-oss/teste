<?php

declare(strict_types=1);

ob_start();
?>
<section class="page-hero">
    <div class="container section-head">
        <span class="eyebrow">Sobre a Fox Delivery</span>
        <h1>Uma plataforma preparada para conectar pedidos, parceiros e entregadores com mais clareza operacional</h1>
        <p>A Fox Delivery foi estruturada para operar jornadas publicas e operacionais com mais organizacao, leitura simples e foco em uma experiencia confiavel para escalar a plataforma.</p>
    </div>
</section>

<section class="container section split">
    <div class="panel">
        <h3>Posicionamento da plataforma</h3>
        <p>A Fox Delivery atua como uma plataforma de conexao entre demanda, operacao comercial e operacao de entregas, com estrutura pensada para apoiar crescimento, cobertura local e relacionamento com diferentes perfis de usuario.</p>
    </div>
    <div class="panel">
        <h3>Compromisso com a operacao</h3>
        <p>As jornadas publicas da Fox Delivery foram organizadas para reduzir atrito na entrada, orientar melhor cada publico e manter integracao com o fluxo administrativo da plataforma.</p>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head centered-head">
            <span class="eyebrow">Pilares da experiencia</span>
            <h2>Como a Fox Delivery estrutura sua presenca publica</h2>
            <p>O site precisa transmitir confianca institucional e ao mesmo tempo orientar a proxima acao de quem compra, vende ou entrega pela plataforma.</p>
        </div>
        <div class="info-card-grid">
            <article class="info-card surface-card">
                <strong>Jornadas separadas</strong>
                <p>Cliente final, loja parceira e entregador seguem caminhos especificos, evitando mistura de mensagens e etapas desnecessarias.</p>
            </article>
            <article class="info-card surface-card">
                <strong>Clareza comercial</strong>
                <p>As telas de entrada da Fox Delivery ajudam o usuario a entender o que acontece antes, durante e depois do cadastro.</p>
            </article>
            <article class="info-card surface-card">
                <strong>Leitura operacional</strong>
                <p>Regras, analise, suporte e orientacoes aparecem com mais contexto para reduzir duvidas e retrabalho.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section">
    <div class="support-callout surface-card">
        <div>
            <span class="eyebrow">Proxima leitura</span>
            <h2>Entenda o funcionamento da plataforma e os canais de apoio</h2>
            <p>Se voce quiser aprofundar o fluxo da Fox Delivery, siga para a pagina de funcionamento da plataforma ou para a central de ajuda.</p>
        </div>
        <div class="partner-final-actions">
            <a class="btn" href="./como-funciona.php" data-track="about_how_click" data-track-component="support_callout">Como funciona</a>
            <a class="btn ghost" href="./ajuda.php" data-track="about_help_click" data-track-component="support_callout">Central de ajuda</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Sobre a Fox';
$current = 'about';
require __DIR__ . '/includes/layout.php';
