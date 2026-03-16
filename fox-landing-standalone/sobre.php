<?php

declare(strict_types=1);

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Sobre Nós</h1>
        <p>Transformando vidas e negócios através da entrega rápida, com integração direta ao ecossistema 6amMart.</p>
    </div>
</section>

<section class="container section">
    <div class="panel">
        <h3>Missão</h3>
        <p>Conectar clientes, lojas e entregadores com uma operação confiável, brasileira e escalável.</p>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Sobre Nós';
$current = 'about';
require __DIR__ . '/includes/layout.php';
