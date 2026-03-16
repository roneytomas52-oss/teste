<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/validators.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $rg = trim((string) ($_POST['rg'] ?? ''));
    $cpf = trim((string) ($_POST['cpf'] ?? ''));
    $cnh = trim((string) ($_POST['cnh'] ?? ''));
    $vehicle = trim((string) ($_POST['vehicle'] ?? ''));
    $pixKey = trim((string) ($_POST['pix_key'] ?? ''));

    if ($name === '') {
        $errors[] = 'Nome completo é obrigatório.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'E-mail inválido.';
    }
    if (strlen(only_digits($phone)) < 10) {
        $errors[] = 'Telefone inválido.';
    }
    if (strlen(only_digits($rg)) < 7) {
        $errors[] = 'RG inválido.';
    }
    if (!is_valid_cpf($cpf)) {
        $errors[] = 'CPF inválido.';
    }
    if ($vehicle !== 'bicicleta' && strlen(only_digits($cnh)) !== 11) {
        $errors[] = 'CNH inválida para veículo motorizado.';
    }
    if ($vehicle === '') {
        $errors[] = 'Modalidade de entrega é obrigatória.';
    }
    if ($pixKey === '') {
        $errors[] = 'Chave Pix é obrigatória.';
    }

    if (!$errors) {
        $payload = [
            'tipo' => 'cadastro_entregador',
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'rg' => $rg,
            'cpf' => $cpf,
            'cnh' => $cnh,
            'vehicle' => $vehicle,
            'pix_key' => $pixKey,
        ];

        try {
            save_contact($name, $email, json_encode($payload, JSON_UNESCAPED_UNICODE) ?: 'cadastro entregador');
            $success = 'Pré-cadastro salvo com sucesso. Agora finalize no cadastro oficial do 6amMart para integração total com o painel.';
        } catch (Throwable) {
            $errors[] = 'Não foi possível salvar o pré-cadastro agora. Verifique o .env.';
        }
    }
}

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Entregador</h1>
        <p>Padrão brasileiro com validação de RG, CPF e CNH conforme operação do 6amMart.</p>
    </div>
</section>

<section class="container section contact">
    <div class="panel">
        <h3>Requisitos obrigatórios (Brasil)</h3>
        <ul class="requirements">
            <li>Documento com foto (RG) e CPF válidos.</li>
            <li>CNH válida para moto/carro.</li>
            <li>Telefone e e-mail ativos.</li>
            <li>Modalidade de entrega (bike, moto ou carro).</li>
            <li>Dados financeiros via chave Pix.</li>
        </ul>
        <p>Depois de validar aqui, conclua no fluxo oficial para registro direto no painel administrativo do 6amMart.</p>
        <p><a class="btn" href="<?= e(sixammart_url('deliveryman/apply')) ?>" target="_blank" rel="noopener noreferrer">Finalizar no cadastro oficial de entregador</a></p>
    </div>

    <form method="POST" class="panel form">
        <?php if ($errors): ?>
            <div class="alert error"><?= e(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert ok"><?= e($success) ?></div>
        <?php endif; ?>

        <label>Nome completo</label>
        <input name="name" required>

        <label>E-mail</label>
        <input type="email" name="email" required>

        <label>Telefone (DDD)</label>
        <input name="phone" placeholder="(11) 99999-9999" required>

        <label>RG</label>
        <input name="rg" placeholder="00.000.000-0" required>

        <label>CPF</label>
        <input name="cpf" placeholder="000.000.000-00" required>

        <label>CNH (obrigatória para moto/carro)</label>
        <input name="cnh" placeholder="00000000000">

        <label>Modalidade de entrega</label>
        <select name="vehicle" required>
            <option value="">Selecione</option>
            <option value="bicicleta">Bicicleta</option>
            <option value="moto">Moto</option>
            <option value="carro">Carro</option>
        </select>

        <label>Chave Pix</label>
        <input name="pix_key" required>

        <button class="btn" type="submit">Salvar pré-cadastro</button>
    </form>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Entregador';
$current = 'delivery';
require __DIR__ . '/includes/layout.php';
