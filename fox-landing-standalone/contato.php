<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$errors = [];
$success = null;
$formInput = [
    'name' => '',
    'email' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $formInput = [
        'name' => $name,
        'email' => $email,
        'message' => $message,
    ];

    if ($name === '') {
        $errors[] = 'Nome e obrigatorio.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail valido.';
    }
    if ($message === '') {
        $errors[] = 'Descreva sua mensagem para o time da Fox Delivery.';
    }

    if (!$errors) {
        try {
            save_contact($name, $email, $message);
            $success = 'Mensagem enviada com sucesso. Nossa equipe fara o retorno pelo canal informado.';
            $formInput = [
                'name' => '',
                'email' => '',
                'message' => '',
            ];
        } catch (Throwable) {
            $errors[] = 'Nao foi possivel registrar sua mensagem agora. Verifique as configuracoes do banco e tente novamente.';
        }
    }
}

ob_start();
?>
<section class="page-hero">
    <div class="container section-head">
        <span class="eyebrow">Atendimento Fox Delivery</span>
        <h1>Fale com a equipe da Fox Delivery</h1>
        <p>Centralize duvidas comerciais, suporte operacional e assuntos institucionais em um unico canal de atendimento da plataforma.</p>
    </div>
</section>

<section class="container section">
    <div class="info-card-grid">
        <article class="info-card surface-card">
            <strong>Comercial</strong>
            <p>Fale sobre expansao da plataforma, novas operacoes, posicionamento de marca e oportunidades de parceria.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Parceiros</strong>
            <p>Use este canal para duvidas sobre cadastro, analise de loja, etapas de aprovacao e orientacao operacional.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Entregadores</strong>
            <p>Envie solicitacoes relacionadas a onboarding, documentacao, validacao e continuidade da jornada operacional.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Institucional</strong>
            <p>Converse com a Fox Delivery sobre atendimento corporativo, suporte institucional e comunicacoes da plataforma.</p>
        </article>
    </div>
</section>

<section class="container section contact" id="duvidas">
    <form method="POST" class="panel form" data-track-form="contact_request">
        <h3>Envie sua mensagem</h3>
        <p>Preencha os campos abaixo para registrar sua solicitacao com a equipe da Fox Delivery.</p>
        <?php if ($errors): ?>
            <div class="alert error"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert ok"><?= e($success) ?></div>
        <?php endif; ?>
        <label>Nome</label>
        <input name="name" value="<?= e($formInput['name']) ?>" placeholder="Seu nome completo" required>
        <label>E-mail</label>
        <input type="email" name="email" value="<?= e($formInput['email']) ?>" placeholder="voce@empresa.com.br" required>
        <label>Mensagem</label>
        <textarea name="message" rows="6" placeholder="Descreva sua necessidade para a equipe da Fox Delivery" required><?= e($formInput['message']) ?></textarea>
        <button class="btn" type="submit" data-track="contact_submit_click" data-track-component="contact_form">Enviar mensagem</button>
    </form>
    <div class="panel contact-info-panel">
        <h3>Como tratamos o atendimento</h3>
        <p>A Fox Delivery centraliza os contatos para organizar a triagem, priorizar o assunto correto e orientar a continuidade de cada solicitacao.</p>
        <ul class="requirements">
            <li>Atendimento comercial para novos parceiros e operacoes</li>
            <li>Orientacao para cadastro, analise e ativacao</li>
            <li>Contato institucional e duvidas gerais sobre a plataforma</li>
        </ul>
        <div class="contact-info-stack">
            <div class="surface-card mini-info-card">
                <strong>Antes de enviar</strong>
                <p>Se sua duvida for recorrente, consulte a central de ajuda para acelerar a resposta.</p>
            </div>
            <div class="surface-card mini-info-card">
                <strong>Fluxo recomendado</strong>
                <p>Parceiros e entregadores tambem podem usar as paginas especificas de jornada para entender melhor cada etapa.</p>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Contato';
$current = 'contact';
require __DIR__ . '/includes/layout.php';
