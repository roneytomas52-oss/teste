<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$vendorApplyUrl = sixammart_url('vendor/apply');

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Lojas</h1>
        <p>Fluxo completo com os mesmos campos oficiais do 6amMart, incluindo mapa, etapas e captcha nativo.</p>
    </div>
</section>

<section class="container section contact registration-layout">
    <div class="panel">
        <h3>Requisitos obrigatórios (Brasil)</h3>
        <ul class="requirements">
            <li>CNPJ ativo e válido.</li>
            <li>Responsável legal e dados de contato.</li>
            <li>Endereço comercial com geolocalização no mapa.</li>
            <li>Inscrição estadual (quando aplicável).</li>
            <li>Documentos, dados bancários e captcha obrigatório.</li>
        </ul>

        <div class="steps-inline">
            <span class="step active">1. Informações gerais</span>
            <span class="step">2. Plano de negócio</span>
            <span class="step">3. Conclusão</span>
        </div>

        <p>O formulário oficial está carregado ao lado, sem redirecionar para outra página. Assim, a validação acontece aqui com o padrão completo do 6amMart.</p>

        <p>
            <a class="btn ghost" href="<?= e($vendorApplyUrl) ?>" target="_blank" rel="noopener noreferrer">
                Abrir em nova aba (fallback)
            </a>
        </p>
    </div>

    <div class="panel embedded-panel">
        <iframe
            class="official-frame"
            src="<?= e($vendorApplyUrl) ?>"
            title="Cadastro oficial de loja"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</section>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Loja';
$current = 'store';
require __DIR__ . '/includes/layout.php';
