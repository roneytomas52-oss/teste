<?php

declare(strict_types=1);

ob_start();
?>
<section class="page-hero">
    <div class="container section-head">
        <span class="eyebrow">Central de ajuda</span>
        <h1>Orienta&ccedil;&otilde;es essenciais para quem usa, vende ou entrega com a Fox Delivery</h1>
        <p>Esta central foi organizada para responder d&uacute;vidas iniciais sobre jornadas, cadastro, an&aacute;lise e contato com a equipe da plataforma.</p>
    </div>
</section>

<section class="container section">
    <div class="section-head">
        <span class="eyebrow">Temas principais</span>
        <h2>O que voc&ecirc; encontra nesta &aacute;rea de ajuda</h2>
        <p>A Fox Delivery trata a base de apoio como parte da experi&ecirc;ncia da plataforma, n&atilde;o apenas como um complemento de atendimento.</p>
    </div>
    <div class="info-card-grid">
        <article class="info-card surface-card">
            <strong>Compra e navega&ccedil;&atilde;o</strong>
            <p>Orienta&ccedil;&otilde;es para quem deseja entender categorias, jornadas e entradas da plataforma.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Cadastro de parceiros</strong>
            <p>D&uacute;vidas sobre decis&atilde;o entre loja e entregador, an&aacute;lise e continuidade do onboarding.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Cadastro de entregadores</strong>
            <p>Explica&ccedil;&atilde;o de etapas, documentos, modalidade e envio para a equipe da Fox Delivery.</p>
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
            <h2>Respostas curtas para as d&uacute;vidas mais comuns</h2>
            <p>Uma base inicial para orientar o usu&aacute;rio antes do contato direto com a equipe da Fox Delivery.</p>
        </div>
        <div class="support-faq-grid">
            <details class="support-faq-item surface-card" open>
                <summary>Onde eu escolho entre cadastro de loja e cadastro de entregador?</summary>
                <p>A entrada correta fica em <a href="./cadastro-parceiros.php">cadastro de parceiros</a>, onde a Fox Delivery direciona cada p&uacute;blico para sua pr&oacute;pria jornada.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>O cadastro da loja e o do entregador s&atilde;o iguais?</summary>
                <p>N&atilde;o. Cada jornada possui contexto, etapas e campos pr&oacute;prios para atender &agrave; necessidade daquele p&uacute;blico.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>O que acontece depois do envio do cadastro?</summary>
                <p>A equipe da Fox Delivery segue com a confer&ecirc;ncia das informa&ccedil;&otilde;es e orienta a continuidade do processo conforme o tipo de jornada.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>Onde posso tirar d&uacute;vidas antes de enviar o cadastro?</summary>
                <p>Use esta central de ajuda ou o canal de <a href="./contato.php#duvidas">Contato</a> para falar com a equipe da Fox Delivery antes de seguir com o cadastro.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>Como a Fox Delivery organiza a entrada de parceiros?</summary>
                <p>A plataforma separa decis&atilde;o, onboarding e orienta&ccedil;&atilde;o para tornar a experi&ecirc;ncia mais clara e menos sujeita a erro.</p>
            </details>
            <details class="support-faq-item surface-card">
                <summary>Se eu n&atilde;o encontrar a resposta aqui, qual &eacute; o pr&oacute;ximo passo?</summary>
                <p>Registre sua necessidade pela p&aacute;gina de contato para que a equipe da Fox Delivery siga com a an&aacute;lise do assunto e retorne pelo canal informado.</p>
            </details>
        </div>
    </div>
</section>

<section class="container section">
    <div class="support-callout surface-card">
        <div>
            <span class="eyebrow">Precisa de apoio</span>
            <h2>Fale com a equipe da Fox Delivery</h2>
            <p>Se sua d&uacute;vida n&atilde;o estiver coberta nesta base inicial, use o canal oficial de contato para suporte comercial, operacional ou institucional.</p>
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
