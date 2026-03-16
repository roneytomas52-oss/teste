<?php

declare(strict_types=1);

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Sobre Nós</h1>
        <p>A Fox Delivery nasceu para acelerar entregas locais com tecnologia, atendimento humano e integração direta ao ecossistema 6amMart.</p>
    </div>
</section>

<section class="container section split">
    <div class="panel">
        <h3>Missão</h3>
        <p>Conectar clientes, lojas e entregadores com operação estável, rápida e escalável para cidades brasileiras.</p>
    </div>
    <div class="panel">
        <h3>Compromisso operacional</h3>
        <p>Cadastros e fluxos sincronizados com o painel administrativo do 6amMart para evitar perda de dados e retrabalho.</p>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Sobre Nós';
$current = 'about';
require __DIR__ . '/includes/layout.php';
