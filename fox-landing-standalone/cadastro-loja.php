<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$vendorApplyUrl = sixammart_url('vendor/apply');
$modulesUrl = sixammart_url('vendor/get-all-modules');
$moduleTypeUrl = sixammart_url('vendor/get-module-type');

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Loja</h1>
        <p>Novo formulário da landing com envio direto para o backend oficial do 6amMart.</p>
    </div>
</section>

<section class="container section">
    <form id="storeForm" class="fox-form" action="<?= e($vendorApplyUrl) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_token" id="store_token">
        <input type="hidden" name="lang[]" value="default">

        <div class="form-head">
            <h3>Dados do responsável</h3>
            <p>Use os mesmos campos exigidos pelo sistema oficial.</p>
        </div>

        <div class="form-grid">
            <label>Nome* <input name="f_name" required></label>
            <label>Sobrenome <input name="l_name"></label>
            <label>E-mail* <input name="email" type="email" required></label>
            <label>Telefone* <input name="phone" required></label>
            <label class="full">Senha* <input name="password" type="password" required></label>
        </div>

        <div class="form-head"><h3>Dados da loja</h3></div>
        <div class="form-grid">
            <label>Nome da loja* <input name="name[]" required></label>
            <label>Endereço* <input name="address[]" required></label>
            <label>Zona* <select name="zone_id" id="zone_id" required><option value="">Selecione</option></select></label>
            <label>Módulo* <select name="module_id" id="module_id" required><option value="">Selecione</option></select></label>
            <label>Latitude* <input name="latitude" required></label>
            <label>Longitude* <input name="longitude" required></label>
            <label>Tempo mínimo* <input type="number" min="0" name="minimum_delivery_time" required></label>
            <label>Tempo máximo* <input type="number" min="0" name="maximum_delivery_time" required></label>
            <label>Tipo de tempo* <select name="delivery_time_type" required><option value="min">min</option><option value="hour">hour</option></select></label>
            <label id="pickup_wrap" class="full" style="display:none">Zona de coleta
                <select name="pickup_zone_id[]" id="pickup_zone_id" multiple></select>
            </label>
        </div>

        <div class="form-head"><h3>Documentos</h3></div>
        <div class="form-grid">
            <label>Logo* <input type="file" name="logo" accept="image/*" required></label>
            <label>Capa <input type="file" name="cover_photo" accept="image/*"></label>
            <label>TIN/CPF/CNPJ <input name="tin"></label>
            <label>Validade TIN <input type="date" name="tin_expire_date"></label>
            <label class="full">Certificado TIN <input type="file" name="tin_certificate_image"></label>
        </div>

        <div class="form-head"><h3>Plano</h3></div>
        <div class="form-grid">
            <label><input type="radio" name="business_plan" value="commission-base" checked> Comissão</label>
            <label><input type="radio" name="business_plan" value="subscription-base"> Assinatura</label>
            <label class="full">Pacote <select name="package_id" id="package_id"><option value="">Selecione</option></select></label>
        </div>

        <div class="form-grid" id="captcha_wrap"></div>

        <button class="btn" type="submit">Enviar cadastro</button>
    </form>
</section>

<footer class="simple-footer"><div class="container"><p>© <?= date('Y') ?> Fox Delivery.</p></div></footer>

<script>
(async function(){
    const sourceUrl = <?= json_encode($vendorApplyUrl) ?>;
    const modulesUrl = <?= json_encode($modulesUrl) ?>;
    const moduleTypeUrl = <?= json_encode($moduleTypeUrl) ?>;
    const form = document.getElementById('storeForm');
    const html = await fetch(sourceUrl, {credentials:'include'}).then(r=>r.text());
    const doc = new DOMParser().parseFromString(html,'text/html');

    const token = doc.querySelector('input[name="_token"]')?.value || '';
    document.getElementById('store_token').value = token;

    const zone = document.getElementById('zone_id');
    const pickup = document.getElementById('pickup_zone_id');
    doc.querySelectorAll('select[name="zone_id"] option').forEach(op=>{
        if(!op.value) return;
        zone.add(new Option(op.textContent.trim(), op.value));
        pickup.add(new Option(op.textContent.trim(), op.value));
    });

    const packageSelect = document.getElementById('package_id');
    doc.querySelectorAll('select[name="package_id"] option').forEach(op=>{
        if(!op.value) return;
        packageSelect.add(new Option(op.textContent.trim(), op.value));
    });

    const captchaWrap = document.getElementById('captcha_wrap');
    const gRecaptcha = doc.querySelector('input[name="g-recaptcha-response"]');
    if (gRecaptcha) {
        captchaWrap.innerHTML = '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">';
    } else {
        const captchaImg = doc.querySelector('img[src^="data:image"]')?.getAttribute('src') || '';
        captchaWrap.innerHTML = `<label>Captcha* <input name="custome_recaptcha" required></label><div class="captcha-box">${captchaImg ? `<img src="${captchaImg}" alt="captcha">` : 'Abra o cadastro oficial para carregar captcha.'}</div>`;
    }

    zone.addEventListener('change', async ()=>{
        const res = await fetch(`${modulesUrl}?zone_id=${zone.value}&q=`, {credentials:'include'});
        const data = await res.json();
        const module = document.getElementById('module_id');
        module.innerHTML = '<option value="">Selecione</option>';
        data.forEach(i => module.add(new Option(i.text, i.id)));
    });

    document.getElementById('module_id').addEventListener('change', async (e)=>{
        const res = await fetch(`${moduleTypeUrl}?id=${e.target.value}`, {credentials:'include'});
        const data = await res.json();
        document.getElementById('pickup_wrap').style.display = data.module_type === 'rental' ? 'block' : 'none';
    });
})();
</script>
<?php if ($recaptchaEnabled && !empty($recaptchaSiteKey)): ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?= e((string)$recaptchaSiteKey) ?>"></script>
<?php endif; ?>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Loja';
$current = 'store';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
