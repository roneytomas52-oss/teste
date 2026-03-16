<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/validators.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $storeName = trim((string) ($_POST['store_name'] ?? ''));
    $ownerName = trim((string) ($_POST['owner_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $cnpj = trim((string) ($_POST['cnpj'] ?? ''));
    $stateRegistration = trim((string) ($_POST['state_registration'] ?? ''));
    $cep = trim((string) ($_POST['cep'] ?? ''));
    $pixKey = trim((string) ($_POST['pix_key'] ?? ''));

    if ($storeName === '') {
        $errors[] = 'Nome da loja é obrigatório.';
    }
    if ($ownerName === '') {
        $errors[] = 'Nome do responsável é obrigatório.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail inválido.';
    }
    if (strlen(only_digits($phone)) < 10) {
        $errors[] = 'Telefone inválido.';
    }
    if (!is_valid_cnpj($cnpj)) {
        $errors[] = 'CNPJ inválido.';
    }
    if (strlen(only_digits($cep)) !== 8) {
        $errors[] = 'CEP inválido.';
    }
    if ($pixKey === '') {
        $errors[] = 'Chave Pix é obrigatória.';
    }

    if (!$errors) {
        $payload = [
            'tipo' => 'cadastro_loja',
            'store_name' => $storeName,
            'owner_name' => $ownerName,
            'email' => $email,
            'phone' => $phone,
            'cnpj' => $cnpj,
            'state_registration' => $stateRegistration,
            'cep' => $cep,
            'pix_key' => $pixKey,
        ];

        try {
            save_contact($ownerName, $email, json_encode($payload, JSON_UNESCAPED_UNICODE) ?: 'cadastro loja');
            $success = 'Pré-cadastro salvo com sucesso. Agora finalize no formulário oficial do 6amMart para integração total com o painel.';
        } catch (Throwable) {
            $errors[] = 'Não foi possível salvar o pré-cadastro agora. Verifique o .env.';
        }
    }
}

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Lojas</h1>
        <p>Padrão brasileiro com validação de CNPJ e envio final no fluxo oficial do 6amMart.</p>
    </div>
</section>

<section class="container section contact">
    <div class="panel">
        <h3>Requisitos obrigatórios (Brasil)</h3>
        <ul class="requirements">
            <li>CNPJ ativo e válido.</li>
            <li>Responsável legal e dados de contato.</li>
            <li>CEP e endereço comercial.</li>
            <li>Inscrição estadual (quando aplicável).</li>
            <li>Dados financeiros com chave Pix.</li>
        </ul>
        <p>Após preencher este pré-cadastro, finalize no formulário oficial para entrar direto no painel administrativo do 6amMart sem perda operacional.</p>
        <p><a class="btn" href="<?= e(sixammart_url('vendor/apply')) ?>" target="_blank" rel="noopener noreferrer">Finalizar no cadastro oficial de loja</a></p>
    </div>

    <form method="POST" class="panel form">
        <?php if ($errors): ?>
            <div class="alert error"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert ok"><?= e($success) ?></div>
        <?php endif; ?>

        <label>Nome da Loja</label>
        <input name="store_name" required>

        <label>Responsável Legal</label>
        <input name="owner_name" required>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Telefone (DDD)</label>
        <input name="phone" placeholder="(11) 99999-9999" required>

        <label>CNPJ</label>
        <input name="cnpj" placeholder="00.000.000/0000-00" required>

        <label>Inscrição Estadual (opcional)</label>
        <input name="state_registration">

        <label>CEP</label>
        <input name="cep" placeholder="00000-000" required>

        <label>Chave Pix</label>
        <input name="pix_key" required>

        <button class="btn" type="submit">Salvar pré-cadastro</button>
    </form>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Loja';
$current = 'store';
require __DIR__ . '/includes/layout.php';
