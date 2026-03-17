<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/database.php';

$vendorApplyUrl = sixammart_url('vendor/apply');
$modulesUrl = sixammart_url('vendor/get-all-modules');
$moduleTypeUrl = sixammart_url('vendor/get-module-type');
$reloadCaptchaUrl = sixammart_url('reload-captcha');

$zones = [];
$packages = [];
$recaptchaConfig = get_business_setting('recaptcha', []);
$recaptchaEnabled = is_array($recaptchaConfig) && (($recaptchaConfig['status'] ?? 0) == 1);
$recaptchaSiteKey = $recaptchaConfig['site_key'] ?? '';

try {
    $zones = db()->query("SELECT id, name FROM zones ORDER BY name ASC")->fetchAll();
} catch (Throwable) {
    $zones = [];
}

try {
    $packages = db()->query("SELECT id, package_name FROM subscription_packages WHERE status = 1 AND module_type = 'all' ORDER BY id DESC")->fetchAll();
} catch (Throwable) {
    $packages = [];
}

ob_start();
?>
<section class="hero small">
    <div class="container">
        <h1>Cadastro de Loja</h1>
        <p>Novo formulário da landing com validação na própria página e envio ao backend do 6amMart.</p>
    </div>
</section>

<section class="container section">
    <form id="storeForm" class="fox-form" action="<?= e($vendorApplyUrl) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_token" id="store_token">
        <input type="hidden" name="lang[]" value="default">

        <div class="form-head">
            <h3>Dados do responsável</h3>
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
            <label>Zona* <select name="zone_id" id="zone_id" required>
                <option value="">Selecione</option>
                <?php foreach ($zones as $zone): ?>
                    <option value="<?= (int)$zone['id'] ?>"><?= e((string)$zone['name']) ?></option>
                <?php endforeach; ?>
            </select></label>
            <label>Módulo* <select name="module_id" id="module_id" required><option value="">Selecione</option></select></label>
            <label>Latitude* <input name="latitude" required></label>
            <label>Longitude* <input name="longitude" required></label>
            <label>Tempo mínimo* <input type="number" min="0" name="minimum_delivery_time" required></label>
            <label>Tempo máximo* <input type="number" min="0" name="maximum_delivery_time" required></label>
            <label>Tipo de tempo* <select name="delivery_time_type" required><option value="min">min</option><option value="hour">hour</option></select></label>
            <label id="pickup_wrap" class="full" style="display:none">Zona de coleta
                <select name="pickup_zone_id[]" id="pickup_zone_id" multiple>
                    <?php foreach ($zones as $zone): ?>
                        <option value="<?= (int)$zone['id'] ?>"><?= e((string)$zone['name']) ?></option>
                    <?php endforeach; ?>
                </select>
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
            <label class="full">Pacote <select name="package_id" id="package_id"><option value="">Selecione</option>
                <?php foreach ($packages as $package): ?>
                    <option value="<?= (int)$package['id'] ?>"><?= e((string)$package['package_name']) ?></option>
                <?php endforeach; ?>
            </select></label>
        </div>

        <div class="form-grid" id="captcha_wrap"></div>

        <button class="btn" type="submit">Enviar cadastro</button>
    </form>
</section>

<footer class="simple-footer"><div class="container"><p>© <?= date('Y') ?> Fox Delivery.</p></div></footer>

<script>
(async function(){
    const modulesUrl = <?= json_encode($modulesUrl) ?>;
    const moduleTypeUrl = <?= json_encode($moduleTypeUrl) ?>;
    const reloadCaptchaUrl = <?= json_encode($reloadCaptchaUrl) ?>;
    const recaptchaEnabled = <?= $recaptchaEnabled ? 'true' : 'false' ?>;
    const recaptchaSiteKey = <?= json_encode($recaptchaSiteKey) ?>;

    const zone = document.getElementById('zone_id');
    const moduleSelect = document.getElementById('module_id');
    const captchaWrap = document.getElementById('captcha_wrap');

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

        captchaWrap.innerHTML = `
            <label>Captcha* <input name="custome_recaptcha" required></label>
            <div class="captcha-box">
                ${img ? `<img src="${img}" alt="captcha">` : '<span>Captcha indisponível</span>'}
                <button type="button" class="captcha-refresh" id="refreshCustomCaptcha">↻</button>
            </div>
        `;

        const token = getCookie('XSRF-TOKEN');
        if (token) document.getElementById('store_token').value = token;

        document.getElementById('refreshCustomCaptcha')?.addEventListener('click', initCustomCaptcha, {once:true});
    }

    async function initRecaptcha() {
        captchaWrap.innerHTML = '<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">';

        if (!window.grecaptcha || !recaptchaSiteKey) return;
        const token = getCookie('XSRF-TOKEN');
        if (token) document.getElementById('store_token').value = token;

        grecaptcha.ready(function () {
            grecaptcha.execute(recaptchaSiteKey, {action: 'submit'}).then(function (token) {
                document.getElementById('g-recaptcha-response').value = token;
            });
        });
    }

    const initSession = await fetch(reloadCaptchaUrl, {credentials:'include'});
    if (initSession.ok) {
        const token = getCookie('XSRF-TOKEN');
        if (token) document.getElementById('store_token').value = token;
    }

    if (recaptchaEnabled) {
        await initRecaptcha();
    } else {
        await initCustomCaptcha();
    }

    zone.addEventListener('change', async ()=>{
        const res = await fetch(`${modulesUrl}?zone_id=${zone.value}&q=`, {credentials:'include'});
        const data = await res.json();
        moduleSelect.innerHTML = '<option value="">Selecione</option>';
        data.forEach(i => moduleSelect.add(new Option(i.text, i.id)));
    });

    moduleSelect.addEventListener('change', async (e)=>{
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
