<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$deliveryApplyUrl = sixammart_url('deliveryman/apply');
$reloadCaptchaUrl = sixammart_url('reload-captcha');

$zones = [];
$vehicles = [];
$recaptchaConfig = get_business_setting('recaptcha', []);
$recaptchaEnabled = is_array($recaptchaConfig) && (($recaptchaConfig['status'] ?? 0) == 1);
$recaptchaSiteKey = $recaptchaConfig['site_key'] ?? '';

try {
    $zones = db()->query("SELECT id, name FROM zones ORDER BY name ASC")->fetchAll();
} catch (Throwable) {
    $zones = [];
}

try {
    $vehicles = db()->query("SELECT id, type FROM vehicles ORDER BY id ASC")->fetchAll();
} catch (Throwable) {
    $vehicles = [];
}

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Entregador</h1>
        <p>Interface moderna conectada ao backend oficial do 6amMart.</p>
    </div>
</section>

<section class="container section">
    <form id="dmForm" class="fox-form" action="<?= e($deliveryApplyUrl) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_token" id="dm_token">

        <div class="form-grid">
            <label>Nome* <input name="f_name" required></label>
            <label>Sobrenome <input name="l_name"></label>
            <label>E-mail* <input type="email" name="email" required></label>
            <label>Telefone* <input name="phone" required></label>
            <label>Tipo de documento
                <select name="identity_type">
                    <option value="passport">passport</option>
                    <option value="driving_license">driving_license</option>
                    <option value="nid">nid</option>
                </select>
            </label>
            <label>Número do documento* <input name="identity_number" required></label>
            <label>Zona* <select name="zone_id" id="dm_zone" required><option value="">Selecione</option></select></label>
            <label>Tipo de entregador* <select name="earning" required><option value="1">freelancer</option><option value="0">salary_based</option></select></label>
            <label>Veículo* <select name="vehicle_id" id="dm_vehicle" required><option value="">Selecione</option></select></label>
            <label>Código de indicação <input name="referral_code"></label>
            <label class="full">Senha* <input type="password" name="password" required></label>
            <label>Foto de perfil <input type="file" name="image" accept="image/*"></label>
            <label>Imagem de documento <input type="file" name="identity_image[]" multiple accept="image/*"></label>
        </div>

        <div class="form-grid" id="dm_captcha_wrap"></div>

        <button class="btn" type="submit">Enviar cadastro</button>
    </form>
</section>

<footer class="simple-footer"><div class="container"><p>© <?= date('Y') ?> Fox Delivery.</p></div></footer>

<script>
(async function(){
    const sourceUrl = <?= json_encode($deliveryApplyUrl) ?>;
    const html = await fetch(sourceUrl, {credentials:'include'}).then(r=>r.text());
    const doc = new DOMParser().parseFromString(html,'text/html');
    document.getElementById('dm_token').value = doc.querySelector('input[name="_token"]')?.value || '';

    const zone = document.getElementById('dm_zone');
    doc.querySelectorAll('select[name="zone_id"] option').forEach(op=>{ if(op.value) zone.add(new Option(op.textContent.trim(), op.value)); });

    const vehicle = document.getElementById('dm_vehicle');
    doc.querySelectorAll('select[name="vehicle_id"] option').forEach(op=>{ if(op.value) vehicle.add(new Option(op.textContent.trim(), op.value)); });

    const wrap = document.getElementById('dm_captcha_wrap');
    if (doc.querySelector('input[name="g-recaptcha-response"]')) {
        wrap.innerHTML = '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">';
    } else {
        const captchaImg = doc.querySelector('img[src^="data:image"]')?.getAttribute('src') || '';
        wrap.innerHTML = `<label>Captcha* <input name="custome_recaptcha" required></label><div class="captcha-box">${captchaImg ? `<img src="${captchaImg}" alt="captcha">` : 'Abra o cadastro oficial para carregar captcha.'}</div>`;
    }
})();
</script>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Entregador';
$current = 'delivery';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
