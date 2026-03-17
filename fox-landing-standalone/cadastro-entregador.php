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
        <p>Interface moderna com validação de captcha na própria página e integração ao 6amMart.</p>
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
            <label>Zona* <select name="zone_id" required>
                <option value="">Selecione</option>
                <?php foreach ($zones as $zone): ?>
                    <option value="<?= (int)$zone['id'] ?>"><?= e((string)$zone['name']) ?></option>
                <?php endforeach; ?>
            </select></label>
            <label>Tipo de entregador* <select name="earning" required><option value="1">freelancer</option><option value="0">salary_based</option></select></label>
            <label>Veículo* <select name="vehicle_id" required>
                <option value="">Selecione</option>
                <?php foreach ($vehicles as $vehicle): ?>
                    <option value="<?= (int)$vehicle['id'] ?>"><?= e((string)$vehicle['type']) ?></option>
                <?php endforeach; ?>
            </select></label>
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
    const reloadCaptchaUrl = <?= json_encode($reloadCaptchaUrl) ?>;
    const recaptchaEnabled = <?= $recaptchaEnabled ? 'true' : 'false' ?>;
    const recaptchaSiteKey = <?= json_encode($recaptchaSiteKey) ?>;
    const wrap = document.getElementById('dm_captcha_wrap');

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
        return '';
    }

    async function initCustomCaptcha() {
        const res = await fetch(reloadCaptchaUrl, {credentials:'include'});
        const data = await res.json();
        const html = new DOMParser().parseFromString(data.view || '', 'text/html');
        const img = html.querySelector('img')?.getAttribute('src') || '';

        wrap.innerHTML = `
            <label>Captcha* <input name="custome_recaptcha" required></label>
            <div class="captcha-box">
                ${img ? `<img src="${img}" alt="captcha">` : '<span>Captcha indisponível</span>'}
                <button type="button" class="captcha-refresh" id="refreshDmCaptcha">↻</button>
            </div>
        `;

        const token = getCookie('XSRF-TOKEN');
        if (token) document.getElementById('dm_token').value = token;

        document.getElementById('refreshDmCaptcha')?.addEventListener('click', initCustomCaptcha, {once:true});
    }

    async function initRecaptcha() {
        wrap.innerHTML = '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">';

        if (!window.grecaptcha || !recaptchaSiteKey) return;
        const token = getCookie('XSRF-TOKEN');
        if (token) document.getElementById('dm_token').value = token;

        grecaptcha.ready(function () {
            grecaptcha.execute(recaptchaSiteKey, {action: 'submit'}).then(function (token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
    }

    const initSession = await fetch(reloadCaptchaUrl, {credentials:'include'});
    if (initSession.ok) {
        const token = getCookie('XSRF-TOKEN');
        if (token) document.getElementById('dm_token').value = token;
    }

    if (recaptchaEnabled) {
        await initRecaptcha();
    } else {
        await initCustomCaptcha();
    }
})();
</script>
<?php if ($recaptchaEnabled && !empty($recaptchaSiteKey)): ?>
<script src="https://www.google.com/recaptcha/api.js?render=<?= e((string)$recaptchaSiteKey) ?>"></script>
<?php endif; ?>
<?php
$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Entregador';
$current = 'delivery';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
