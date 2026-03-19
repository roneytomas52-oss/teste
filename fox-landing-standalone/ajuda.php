<?php

declare(strict_types=1);

ob_start();
?>
<section class="page-hero">
    <div class="container section-head">
        <span class="eyebrow">Central de ajuda</span>
        <h1>Orientacoes essenciais para quem usa, vende ou entrega com a Fox Delivery</h1>
        <p>Esta central foi organizada para responder duvidas iniciais sobre jornadas, cadastro, analise e contato com a equipe da plataforma.</p>
    </div>
</section>

<section class="container section">
    <div class="section-head">
        <span class="eyebrow">Temas principais</span>
        <h2>O que voce encontra nesta area de ajuda</h2>
        <p>A Fox Delivery trata a base de apoio como parte da experiencia da plataforma, nao apenas como um complemento de atendimento.</p>
    </div>
    <div class="info-card-grid">
        <article class="info-card surface-card">
            <strong>Compra e navegacao</strong>
            <p>Orientacoes para quem deseja entender categorias, jornadas e entradas da plataforma.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Cadastro de parceiros</strong>
            <p>Duvidas sobre decisao entre loja e entregador, analise e continuidade do onboarding.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Cadastro de entregadores</strong>
            <p>Explicacao de etapas, documentos, modalidade e envio para a equipe da Fox Delivery.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Suporte institucional</strong>
            <p>Contato para assuntos comerciais, operacionais e gerais sobre a plataforma.</p>
        </article>
    </div>
</section>

<section class="section section-soft">
    <div class="container">
        <div class="section-head centered-head">
            <span class="eyebrow">Perguntas frequentes</span>
            <h2>Respostas curtas para as duvidas mais comuns</h2>
            <p>Uma base inicial para orientar o usuario antes do contato direto com a equipe da Fox Delivery.</p>
        </div>
        <div class="support-faq-grid">
            <details class="support-faq-item surface-card" open>
                <summary>Onde eu escolho entre cadastro de loja e cadastro de entregador?</summary>
                <p>A entrada correta fica em <a href="./cadastro-parceiros.php">cadastro de parceiros</a>, onde a Fox Delivery direciona cada publico para sua propria jornada.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>O cadastro da loja e o do entregador sao iguais?</summary>
                <p>Nao. Cada jornada possui contexto, etapas e campos proprios para atender a necessidade daquele publico.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>O que acontece depois do envio do cadastro?</summary>
                <p>A equipe da Fox Delivery segue com a conferencia das informacoes e orienta a continuidade do processo conforme o tipo de jornada.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>Onde posso tirar duvidas antes de enviar o cadastro?</summary>
                <p>Use esta central de ajuda, a pagina <a href="./como-funciona.php">Como funciona</a> ou o canal de <a href="./contato.php#duvidas">Contato</a>.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>Como a Fox Delivery organiza a entrada de parceiros?</summary>
                <p>A plataforma separa decisao, onboarding e orientacao para tornar a experiencia mais clara e menos sujeita a erro.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>Se eu nao encontrar a resposta aqui, qual o proximo passo?</summary>
                <p>Registre sua necessidade pela pagina de contato para que a equipe da Fox Delivery analise o assunto e retorne pelo canal informado.</p>
            </details>
        </div>
    </div>
</section>

<section class="container section">
    <div class="support-callout surface-card">
        <div>
            <span class="eyebrow">Precisa de apoio</span>
            <h2>Fale com a equipe da Fox Delivery</h2>
            <p>Se sua duvida nao estiver coberta nesta base inicial, use o canal oficial de contato para suporte comercial, operacional ou institucional.</p>
        </div>
        <div class="partner-final-actions">
            <a class="btn" href="./contato.php#duvidas" data-track="help_contact_click" data-track-component="support_callout">Entrar em contato</a>
            <a class="btn ghost" href="./sobre.php" data-track="help_about_click" data-track-component="support_callout">Sobre a plataforma</a>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Ajuda';
$current = 'help';
require __DIR__ . '/includes/layout.php';
