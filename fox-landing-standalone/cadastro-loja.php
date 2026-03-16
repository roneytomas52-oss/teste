<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';
$flutterSettings = get_data_settings_by_type('flutter_landing_page');
$storeRegisterUrl = !empty($flutterSettings['join_seller_button_url'])
    ? (string) $flutterSettings['join_seller_button_url']
    : sixammart_url('vendor/apply');

ob_start();
?>
<section class="container section">
    <h1>Cadastro de Loja</h1>
    <p>URL sincronizada com o painel 6amMart (chave: <code>join_seller_button_url</code>).</p>
    <p><a class="btn" href="<?= e($storeRegisterUrl) ?>" target="_blank">Abrir cadastro oficial de loja</a></p>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Loja';
$current = 'store';
require __DIR__ . '/includes/layout.php';
