<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';
$flutterSettings = get_data_settings_by_type('flutter_landing_page');
$deliveryRegisterUrl = !empty($flutterSettings['join_delivery_man_button_url'])
    ? (string) $flutterSettings['join_delivery_man_button_url']
    : sixammart_url('deliveryman/apply');

ob_start();
?>
<section class="container section">
    <h1>Cadastro de Entregador</h1>
    <p>URL sincronizada com o painel 6amMart (chave: <code>join_delivery_man_button_url</code>).</p>
    <p><a class="btn" href="<?= e($deliveryRegisterUrl) ?>" target="_blank">Abrir cadastro oficial de entregador</a></p>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Entregador';
$current = 'delivery';
require __DIR__ . '/includes/layout.php';
