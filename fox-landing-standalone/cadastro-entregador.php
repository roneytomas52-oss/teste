<?php

declare(strict_types=1);

ob_start();
?>
<section class="container section">
    <h1>Cadastro de Entregador</h1>
    <p>Para garantir sincronização total com painel e banco 6amMart, o botão abaixo abre o fluxo oficial:</p>
    <p><a class="btn" href="<?= e(sixammart_url('deliveryman/apply')) ?>" target="_blank">Abrir cadastro oficial de entregador</a></p>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Entregador';
$current = 'delivery';
require __DIR__ . '/includes/layout.php';
