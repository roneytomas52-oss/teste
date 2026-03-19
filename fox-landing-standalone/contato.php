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
        $errors[] = 'Nome é obrigatório.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail válido.';
    }
    if ($message === '') {
        $errors[] = 'Descreva sua mensagem para o time da Fox Delivery.';
    }

    if (!$errors) {
        try {
            save_contact($name, $email, $message);
            $success = 'Mensagem enviada com sucesso. Nossa equipe fará o retorno pelo canal informado.';
            $formInput = [
                'name' => '',
                'email' => '',
                'message' => '',
            ];
        } catch (Throwable) {
            $errors[] = 'Não foi possível registrar sua mensagem agora. Verifique as configurações do banco e tente novamente.';
        }
    }
}

ob_start();
?>
<section class="page-hero">
    <div class="container section-head">
        <span class="eyebrow">Atendimento Fox Delivery</span>
        <h1>Fale com a equipe da Fox Delivery</h1>
        <p>Centralize d&uacute;vidas comerciais, suporte operacional e assuntos institucionais em um &uacute;nico canal de atendimento da plataforma.</p>
    </div>
</section>

<section class="container section">
    <div class="info-card-grid">
        <article class="info-card surface-card">
            <strong>Comercial</strong>
            <p>Fale sobre expans&atilde;o da plataforma, novas opera&ccedil;&otilde;es, posicionamento de marca e oportunidades de parceria.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Parceiros</strong>
            <p>Use este canal para d&uacute;vidas sobre cadastro, an&aacute;lise de loja, etapas de aprova&ccedil;&atilde;o e orienta&ccedil;&atilde;o operacional.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Entregadores</strong>
            <p>Envie solicita&ccedil;&otilde;es relacionadas a onboarding, documenta&ccedil;&atilde;o, valida&ccedil;&atilde;o e continuidade da jornada operacional.</p>
        </article>
        <article class="info-card surface-card">
            <strong>Institucional</strong>
            <p>Converse com a Fox Delivery sobre atendimento corporativo, suporte institucional e comunica&ccedil;&otilde;es da plataforma.</p>
        </article>
    </div>
</section>

<section class="container section contact" id="duvidas">
    <form method="POST" class="panel form" data-track-form="contact_request">
        <h3>Envie sua mensagem</h3>
        <p>Preencha os campos abaixo para registrar sua solicita&ccedil;&atilde;o com a equipe da Fox Delivery.</p>
        <?php if ($errors): ?>
            <div class="alert error"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert ok"><?= e($success) ?></div>
        <?php endif; ?>
        <label>Nome</label>
        <input name="name" value="<?= e($formInput['name']) ?>" placeholder="Seu nome completo" required>
        <label>E-mail</label>
        <input type="email" name="email" value="<?= e($formInput['email']) ?>" placeholder="voc&ecirc;@empresa.com.br" required>
        <label>Mensagem</label>
        <textarea name="message" rows="6" placeholder="Descreva sua necessidade para a equipe da Fox Delivery" required><?= e($formInput['message']) ?></textarea>
        <button class="btn" type="submit" data-track="contact_submit_click" data-track-component="contact_form">Enviar mensagem</button>
    </form>
    <div class="panel contact-info-panel">
        <h3>Como tratamos o atendimento</h3>
        <p>A Fox Delivery centraliza os contatos para organizar a triagem, priorizar o assunto correto e orientar a continuidade de cada solicita&ccedil;&atilde;o.</p>
        <ul class="requirements">
            <li>Atendimento comercial para novos parceiros e opera&ccedil;&otilde;es</li>
            <li>Orienta&ccedil;&atilde;o para cadastro, an&aacute;lise e ativa&ccedil;&atilde;o</li>
            <li>Contato institucional e d&uacute;vidas gerais sobre a plataforma</li>
        </ul>
        <div class="contact-info-stack">
            <div class="surface-card mini-info-card">
                <strong>Antes de enviar</strong>
                <p>Se sua d&uacute;vida for recorrente, consulte a central de ajuda para acelerar a resposta.</p>
            </div>
            <div class="surface-card mini-info-card">
                <strong>Fluxo recomendado</strong>
                <p>Parceiros e entregadores tamb&eacute;m podem usar as p&aacute;ginas espec&iacute;ficas de jornada para entender melhor cada etapa.</p>
            </div>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Contato';
$current = 'contact';
require __DIR__ . '/includes/layout.php';
