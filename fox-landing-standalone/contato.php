<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($name === '') {
        $errors[] = 'Nome é obrigatório.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    }
    if ($message === '') {
        $errors[] = 'Mensagem é obrigatória.';
    }

    if (!$errors) {
        try {
            save_contact($name, $email, $message);
            $success = 'Mensagem enviada com sucesso.';
        } catch (Throwable) {
            $errors[] = 'Não foi possível salvar agora. Verifique as configurações do banco no .env.';
        }
    }
}

ob_start();
?>
<section class="container section contact">
    <div>
        <h1>Contato</h1>
        <p>Fale com o time da Fox Delivery para suporte, parcerias e dúvidas comerciais.</p>
    </div>
    <form method="POST" class="panel form">
        <?php if ($errors): ?>
            <div class="alert error"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert ok"><?= e($success) ?></div>
        <?php endif; ?>
        <label>Nome</label>
        <input name="name" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Mensagem</label>
        <textarea name="message" rows="5" required></textarea>
        <button class="btn" type="submit">Enviar mensagem</button>
    </form>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Contato';
$current = 'contact';
require __DIR__ . '/includes/layout.php';
