<?php

declare(strict_types=1);

ob_start();
?>
<section class="page-hero">
    <div class="container section-head">
        <span class="eyebrow">Sobre a Fox Delivery</span>
        <h1>Uma plataforma preparada para conectar pedidos, parceiros e entregadores com mais clareza operacional</h1>
        <p>A Fox Delivery foi estruturada para operar jornadas públicas e operacionais com mais organização, leitura simples e foco em uma experiência confiável para escalar a plataforma.</p>
    </div>
</section>

<section class="container section split">
    <div class="panel">
        <h3>Posicionamento da plataforma</h3>
        <p>A Fox Delivery atua como uma plataforma de conexão entre demanda, operação comercial e operação de entregas, com estrutura pensada para apoiar crescimento, cobertura local e relacionamento com diferentes perfis de usuário.</p>
    </div>
    <div class="panel">
        <h3>Compromisso com a operação</h3>
        <p>As jornadas públicas da Fox Delivery foram organizadas para reduzir atrito na entrada, orientar melhor cada público e manter integração com o fluxo administrativo da plataforma.</p>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head centered-head">
            <span class="eyebrow">Pilares da experiência</span>
            <h2>Como a Fox Delivery estrutura sua presença pública</h2>
            <p>O site precisa transmitir confiança institucional e, ao mesmo tempo, orientar a próxima ação de quem compra, vende ou entrega pela plataforma.</p>
        </div>
        <div class="info-card-grid">
            <article class="info-card surface-card">
                <strong>Jornadas separadas</strong>
                <p>Cliente final, loja parceira e entregador seguem caminhos específicos, evitando mistura de mensagens e etapas desnecessárias.</p>
            </article>
            <article class="info-card surface-card">
                <strong>Clareza comercial</strong>
                <p>As telas de entrada da Fox Delivery ajudam o usuário a entender o que acontece antes, durante e depois do cadastro.</p>
            </article>
            <article class="info-card surface-card">
                <strong>Leitura operacional</strong>
                <p>Regras, análise, suporte e orientações aparecem com mais contexto para reduzir dúvidas e retrabalho.</p>
            </article>
        </div>
    </div>
</section>

<section class="container section">
    <div class="support-callout surface-card">
        <div>
            <span class="eyebrow">Próximo acesso</span>
            <h2>Acesse os canais de apoio da plataforma</h2>
            <p>Se você quiser aprofundar as orientações da Fox Delivery, siga para a central de ajuda ou entre em contato com a equipe da plataforma.</p>
        </div>
        <div class="partner-final-actions">
            <a class="btn" href="./ajuda.php" data-track="about_help_click" data-track-component="support_callout">Central de ajuda</a>
            <a class="btn ghost" href="./contato.php" data-track="about_contact_click" data-track-component="support_callout">Fale com a equipe</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Sobre a Fox';
$current = 'about';
require __DIR__ . '/includes/layout.php';
