<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

ob_start();
?>
<style>
.fdxp-page{
    --fdxp-red:#c71516;
    --fdxp-red-strong:#e52f18;
    --fdxp-orange:#ff7d24;
    --fdxp-orange-soft:#ffb14b;
    --fdxp-cream:#fff7f2;
    --fdxp-paper:#fffdfb;
    --fdxp-ink:#5a2a20;
    --fdxp-muted:#7b584d;
    --fdxp-line:rgba(241,211,194,.92);
    --fdxp-shadow:0 30px 58px rgba(118,38,14,.11);
    --fdxp-shell:1200px;
    position:relative;
    overflow:hidden;
    background:linear-gradient(180deg,#fff8f4 0%,#fff6f1 100%);
    color:var(--fdxp-ink);
    font-family:"Segoe UI Variable Display","Segoe UI","Helvetica Neue",Arial,sans-serif;
    line-height:1.5;
}

.fdxp-shell{
    width:min(var(--fdxp-shell),calc(100vw - 56px));
    margin:0 auto;
}

.fdxp-hero{
    position:relative;
    overflow:hidden;
    padding:24px 0 160px;
    color:#fff;
    background:
        radial-gradient(circle at 18% 18%,rgba(255,196,123,.16) 0,rgba(255,196,123,0) 24%),
        radial-gradient(circle at 82% 14%,rgba(255,241,221,.22) 0,rgba(255,241,221,0) 18%),
        radial-gradient(circle at 62% 58%,rgba(255,192,109,.1) 0,rgba(255,192,109,0) 24%),
        linear-gradient(180deg,var(--fdxp-red) 0%,var(--fdxp-red-strong) 30%,var(--fdxp-orange) 70%,#f4c89b 100%);
}

.fdxp-hero::before{
    content:"";
    position:absolute;
    inset:0;
    background:
        linear-gradient(110deg,rgba(86,15,8,.3) 0,rgba(86,15,8,0) 42%),
        radial-gradient(circle at 12% 88%,rgba(86,10,13,.4) 0,rgba(86,10,13,0) 34%),
        linear-gradient(0deg,rgba(110,40,18,.14) 0,rgba(110,40,18,0) 24%);
}

.fdxp-hero::after{
    content:"";
    position:absolute;
    left:-8%;
    right:-8%;
    bottom:72px;
    height:156px;
    background:
        linear-gradient(180deg,rgba(255,233,205,.12) 0,rgba(255,233,205,0) 42%),
        linear-gradient(92deg,rgba(82,28,18,.7) 0%,rgba(138,57,24,.42) 30%,rgba(255,150,63,.08) 100%);
    transform:skewY(-8deg);
    opacity:.72;
}

.fdxp-topbar,
.fdxp-hero-grid{
    position:relative;
    z-index:2;
}

.fdxp-topbar{
    display:grid;
    grid-template-columns:auto 1fr auto;
    align-items:center;
    gap:24px;
    margin-bottom:46px;
}

.fdxp-brand{
    display:inline-flex;
    align-items:center;
    text-decoration:none;
    width:max-content;
    padding:10px;
    border-radius:30px;
    background:linear-gradient(180deg,rgba(255,248,241,.14),rgba(255,239,230,.07));
    border:1px solid rgba(255,255,255,.14);
    box-shadow:0 18px 34px rgba(91,20,9,.16);
    backdrop-filter:blur(12px);
}

.fdxp-brand img{
    display:block;
    width:94px;
    height:94px;
    max-width:none;
    object-fit:cover;
    object-position:center;
    border-radius:24px;
    box-shadow:0 12px 24px rgba(75,24,12,.18);
    filter:saturate(1.03) contrast(1.02) brightness(1.01);
}

.fdxp-nav{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:22px;
    flex-wrap:wrap;
}

.fdxp-nav a{
    color:rgba(255,247,241,.94);
    text-decoration:none;
    font-size:15px;
    font-weight:700;
    letter-spacing:-.01em;
}

.fdxp-nav a:first-child::before{
    content:none;
}

.fdxp-nav a:nth-child(2)::before{
    content:none;
}

.fdxp-actions{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:10px;
    flex-wrap:wrap;
}

.fdxp-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:0;
    padding:13px 20px;
    border-radius:999px;
    background:linear-gradient(180deg,#ff6921,#de2715);
    border:1px solid rgba(255,255,255,.18);
    box-shadow:0 18px 30px rgba(120,20,8,.2);
    color:#fff;
    text-decoration:none;
    font-size:16px;
    font-weight:900;
    transition:transform .2s ease,box-shadow .2s ease;
}

.fdxp-pill:hover,
.fdxp-button:hover{
    transform:translateY(-1px);
}

.fdxp-pill-soft{
    background:rgba(255,247,241,.1);
    border-color:rgba(255,255,255,.14);
    box-shadow:none;
    backdrop-filter:blur(10px);
}

.fdxp-pill-light{
    background:rgba(255,247,242,.96);
    color:#aa3d1a;
    box-shadow:0 12px 24px rgba(123,27,11,.12);
}

.fdxp-pill span{
    margin-left:8px;
    font-size:20px;
    line-height:1;
}

.fdxp-hero-grid{
    display:grid;
    grid-template-columns:.94fr 1.06fr;
    gap:36px;
    align-items:center;
    min-height:456px;
}

.fdxp-copy{
    max-width:480px;
    padding:10px 0 40px;
}

.fdxp-copy h1{
    margin:0 0 16px;
    font-size:70px;
    line-height:.94;
    letter-spacing:-.06em;
    text-shadow:0 10px 26px rgba(109,18,10,.16);
}

.fdxp-copy p{
    margin:0;
    max-width:390px;
    font-size:21px;
    line-height:1.58;
    color:rgba(255,247,241,.98);
}

.fdxp-scene{
    position:relative;
    display:flex;
    justify-content:flex-end;
    align-items:center;
    min-height:404px;
    padding-bottom:4px;
}

.fdxp-scene::before{
    content:"";
    position:absolute;
    inset:auto 14% 20% 10%;
    height:56%;
    border-radius:50%;
    background:radial-gradient(circle at center,rgba(255,237,215,.34) 0,rgba(255,237,215,.09) 42%,rgba(255,237,215,0) 74%);
}

.fdxp-scene::after{
    content:"";
    position:absolute;
    bottom:18%;
    width:52%;
    height:22px;
    border-radius:50%;
    background:rgba(91,23,12,.1);
    filter:blur(14px);
}

.fdxp-spotlight{
    position:relative;
    z-index:2;
    width:min(100%,446px);
    display:grid;
    gap:14px;
    padding:34px 32px;
    border-radius:32px;
    background:
        linear-gradient(180deg,rgba(255,252,249,.95),rgba(255,244,236,.9)),
        linear-gradient(180deg,rgba(255,255,255,.12),rgba(255,255,255,.04));
    border:1px solid rgba(255,255,255,.26);
    box-shadow:0 32px 56px rgba(92,22,11,.15);
    backdrop-filter:blur(16px);
}

.fdxp-spotlight::before{
    content:"";
    position:absolute;
    inset:14px;
    border-radius:24px;
    border:1px solid rgba(255,255,255,.48);
    opacity:.5;
}

.fdxp-spotlight::after{
    content:"";
    position:absolute;
    inset:auto 28px -16px auto;
    width:116px;
    height:116px;
    border-radius:50%;
    background:radial-gradient(circle at center,rgba(255,169,80,.3) 0,rgba(255,169,80,.08) 46%,rgba(255,169,80,0) 74%);
    filter:blur(6px);
}

.fdxp-spotlight-tag{
    position:relative;
    z-index:1;
    display:inline-flex;
    width:max-content;
    padding:10px 14px;
    border-radius:999px;
    background:#fff4ec;
    color:#ca4f1d;
    font-size:12px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.fdxp-spotlight strong{
    position:relative;
    z-index:1;
    color:#562920;
    font-size:32px;
    line-height:1.1;
    letter-spacing:-.04em;
}

.fdxp-spotlight p{
    position:relative;
    z-index:1;
    margin:0;
    color:#6f4e42;
    font-size:15px;
    line-height:1.72;
    max-width:35ch;
}

.fdxp-curve{
    position:absolute;
    inset:auto 0 0;
    z-index:2;
    height:140px;
    pointer-events:none;
}

.fdxp-curve::before{
    content:"";
    position:absolute;
    inset:34px -5% -10px;
    background:#fff9f6;
    border-radius:52% 48% 0 0 / 100% 100% 0 0;
}

.fdxp-curve::after{
    content:"";
    position:absolute;
    inset:0 0 28px;
    background:
        linear-gradient(90deg,rgba(255,101,88,.84) 0,rgba(255,101,88,.84) 10%,transparent 10%),
        linear-gradient(90deg,rgba(255,204,200,.78) 0,rgba(255,204,200,.78) 18%,transparent 18%);
    border-radius:48% 52% 0 0 / 100% 100% 0 0;
    opacity:.84;
}

.fdxp-main{
    position:relative;
    margin-top:-64px;
    padding:0 0 96px;
    background:linear-gradient(180deg,#fff8f4 0%,#fff9f6 58%,#f4d7c1 100%);
}

.fdxp-main-shell{
    position:relative;
    z-index:3;
    display:grid;
    gap:28px;
}

.fdxp-heading{
    text-align:center;
}

.fdxp-heading h2{
    margin:0;
    color:#55291f;
    font-size:56px;
    line-height:1;
    letter-spacing:-.05em;
}

.fdxp-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:26px;
    align-items:stretch;
}

.fdxp-card{
    position:relative;
    display:grid;
    gap:24px;
    min-height:100%;
    overflow:hidden;
    isolation:isolate;
    padding:36px 34px 34px;
    border-radius:32px;
    background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(255,248,244,.97));
    border:1px solid var(--fdxp-line);
    box-shadow:0 26px 44px rgba(118,38,14,.11);
}

.fdxp-card::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:5px;
    border-radius:32px 32px 0 0;
    background:linear-gradient(90deg,#ff7a24,#ea2f17);
}

.fdxp-card::after{
    content:"";
    position:absolute;
    inset:auto -10% 56% auto;
    width:180px;
    height:180px;
    border-radius:50%;
    background:radial-gradient(circle at center,rgba(255,173,104,.18) 0,rgba(255,173,104,.06) 42%,rgba(255,173,104,0) 72%);
    z-index:-1;
}

.fdxp-card-rider::before{
    background:linear-gradient(90deg,#ff982c,#f04c18);
}

.fdxp-card-head{
    display:grid;
    gap:14px;
}

.fdxp-card-label{
    display:inline-flex;
    width:max-content;
    padding:9px 13px;
    border-radius:999px;
    background:#fff2ea;
    color:#cb501e;
    font-size:12px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.fdxp-card-label.rider{
    background:#fff0e7;
    color:#da5a20;
}

.fdxp-card-head h3{
    margin:0;
    color:#55291f;
    font-size:31px;
    line-height:1.1;
    letter-spacing:-.03em;
}

.fdxp-card-head p{
    margin:0;
    color:#714d41;
    font-size:16px;
    line-height:1.7;
    max-width:32ch;
}

.fdxp-checks{
    display:grid;
    gap:14px;
    margin:0;
    padding:0;
    list-style:none;
}

.fdxp-checks li{
    position:relative;
    padding-left:36px;
    color:#4d291d;
    font-size:17px;
    font-weight:800;
    line-height:1.4;
}

.fdxp-checks li::before{
    content:"";
    position:absolute;
    left:4px;
    top:7px;
    width:14px;
    height:8px;
    border-left:4px solid #79a653;
    border-bottom:4px solid #79a653;
    transform:rotate(-45deg);
}

.fdxp-button{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:100%;
    padding:19px 24px;
    border-radius:999px;
    background:linear-gradient(180deg,#ff6a20,#ea2916);
    border:1px solid rgba(255,255,255,.18);
    color:#fff;
    text-decoration:none;
    font-size:20px;
    font-weight:900;
    letter-spacing:-.02em;
    box-shadow:0 20px 34px rgba(188,41,15,.24);
    transition:transform .2s ease,box-shadow .2s ease;
}

.fdxp-button-rider{
    background:linear-gradient(180deg,#ff8d21,#f04d18);
}

.fdxp-integration{
    display:grid;
    grid-template-columns:auto 1fr;
    gap:20px;
    align-items:center;
    max-width:1020px;
    width:100%;
    margin:8px auto 0;
    padding:30px 36px;
    border-radius:28px;
    background:rgba(255,249,245,.97);
    border:1px solid rgba(241,215,198,.98);
    box-shadow:0 20px 34px rgba(143,60,20,.08);
}

.fdxp-integration-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:78px;
    height:78px;
    border-radius:20px;
    background:linear-gradient(180deg,#ffb438,#f08d18);
    box-shadow:0 14px 24px rgba(241,139,24,.22);
}

.fdxp-integration-icon span{
    width:30px;
    height:18px;
    border-left:6px solid #fff;
    border-bottom:6px solid #fff;
    transform:rotate(-45deg) translateY(-2px);
}

.fdxp-integration-copy h3{
    margin:0 0 6px;
    color:#582a20;
    font-size:28px;
    line-height:1.1;
    letter-spacing:-.03em;
}

.fdxp-integration-copy p{
    margin:0;
    color:#856356;
    font-size:16px;
    line-height:1.65;
}

.fdxp-footer{
    position:relative;
    background:linear-gradient(180deg,rgba(97,28,18,.98),rgba(70,20,13,.99));
    color:rgba(255,241,233,.92);
}

.fdxp-footer::before{
    content:"";
    position:absolute;
    inset:0;
    background:
        radial-gradient(circle at 18% 10%,rgba(255,138,63,.18) 0,rgba(255,138,63,0) 24%),
        radial-gradient(circle at 82% 0,rgba(255,193,128,.12) 0,rgba(255,193,128,0) 20%);
}

.fdxp-footer-shell{
    position:relative;
    z-index:1;
    display:grid;
    grid-template-columns:1.1fr .9fr;
    gap:28px;
    align-items:start;
    padding:34px 0 40px;
}

.fdxp-footer-copy{
    display:grid;
    gap:10px;
    max-width:520px;
}

.fdxp-footer-copy strong{
    font-size:26px;
    line-height:1;
    letter-spacing:-.04em;
    color:#fff7f2;
}

.fdxp-footer-copy p{
    margin:0;
    font-size:15px;
    line-height:1.75;
    color:rgba(255,239,231,.8);
}

.fdxp-footer-links{
    display:flex;
    flex-wrap:wrap;
    justify-content:flex-end;
    gap:12px 16px;
}

.fdxp-footer-links a{
    color:rgba(255,247,241,.9);
    text-decoration:none;
    font-size:15px;
    font-weight:700;
}

@media(max-width:1100px){
    .fdxp-topbar{
        grid-template-columns:1fr;
        justify-items:center;
    }

    .fdxp-nav,
    .fdxp-actions{
        justify-content:center;
    }

    .fdxp-hero-grid,
    .fdxp-grid,
    .fdxp-integration,
    .fdxp-footer-shell{
        grid-template-columns:1fr;
    }

    .fdxp-copy{
        max-width:none;
        text-align:center;
        padding-bottom:26px;
    }

    .fdxp-copy p{
        margin:0 auto;
    }

    .fdxp-scene{
        justify-content:center;
        min-height:320px;
        padding-bottom:0;
    }

    .fdxp-footer-copy,
    .fdxp-footer-links{
        justify-content:center;
        text-align:center;
    }

}

@media(max-width:720px){
    .fdxp-shell{
        width:min(var(--fdxp-shell),calc(100vw - 28px));
    }

    .fdxp-hero{
        padding:20px 0 118px;
    }

    .fdxp-hero::after{
        bottom:48px;
        height:118px;
    }

    .fdxp-topbar{
        margin-bottom:28px;
    }

    .fdxp-brand img{
        width:82px;
        height:82px;
    }

    .fdxp-nav{
        gap:14px;
    }

    .fdxp-nav a,
    .fdxp-pill{
        font-size:15px;
    }

    .fdxp-pill{
        min-width:112px;
        padding:12px 16px;
    }

    .fdxp-copy h1{
        font-size:52px;
    }

    .fdxp-copy p{
        font-size:18px;
    }

    .fdxp-scene{
        min-height:220px;
    }

    .fdxp-spotlight{
        width:100%;
        padding:22px 20px;
    }

    .fdxp-spotlight strong{
        font-size:24px;
    }

    .fdxp-spotlight p{
        max-width:none;
    }

    .fdxp-curve{
        height:96px;
    }

    .fdxp-main{
        margin-top:-52px;
        padding-bottom:72px;
    }

    .fdxp-heading h2{
        font-size:38px;
    }

    .fdxp-card,
    .fdxp-integration{
        padding:24px 22px;
    }

    .fdxp-card-head h3{
        font-size:24px;
    }

    .fdxp-checks li{
        font-size:17px;
    }

    .fdxp-button{
        font-size:18px;
    }

    .fdxp-integration-icon{
        width:74px;
        height:74px;
    }

    .fdxp-integration-copy h3{
        font-size:24px;
    }

    .fdxp-footer-shell{
        padding:28px 0 32px;
        gap:18px;
    }

    .fdxp-footer-copy strong{
        font-size:22px;
    }

    .fdxp-footer-copy p,
    .fdxp-footer-links a{
        font-size:14px;
    }
}
</style>

<section class="fdxp-page">
    <section class="fdxp-hero">
        <div class="fdxp-shell">
            <div class="fdxp-topbar">
                <a class="fdxp-brand" href="./index.php" aria-label="Fox Delivery">
                    <img src="./Imagem/logo.png" alt="Fox Delivery">
                </a>

                <nav class="fdxp-nav" aria-label="Navega&ccedil;&atilde;o do parceiro">
                    <a href="./cadastro-parceiros.php">Menu parceiro</a>
                    <a href="./index.php#blog">Blog</a>
                    <a href="./sobre.php">Sobre n&oacute;s</a>
                </nav>

                <div class="fdxp-actions">
                    <a class="fdxp-pill fdxp-pill-soft" href="./index.php#apps">Baixar App</a>
                    <a class="fdxp-pill fdxp-pill-light" href="#cadastro-opcoes">Cadastrar <span aria-hidden="true">&rsaquo;</span></a>
                    <a class="fdxp-pill" href="<?= e(sixammart_url('login')) ?>">Entrar</a>
                </div>
            </div>

            <div class="fdxp-hero-grid">
                <div class="fdxp-copy">
                    <h1>Cadastre-se<br>na Fox Delivery</h1>
                    <p>Escolha a modalidade de parceria ideal para integrar sua opera&ccedil;&atilde;o &agrave; plataforma Fox Delivery.</p>
                </div>

                <div class="fdxp-scene">
                    <div class="fdxp-spotlight">
                        <span class="fdxp-spotlight-tag">Cadastro de parceiros Fox Delivery</span>
                        <strong>Credencie sua opera&ccedil;&atilde;o comercial ou log&iacute;stica com a estrutura oficial da Fox Delivery.</strong>
                        <p>Fluxo institucional com valida&ccedil;&atilde;o centralizada, acompanhamento administrativo e integra&ccedil;&atilde;o ao ecossistema da plataforma.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="fdxp-curve"></div>
    </section>

    <section class="fdxp-main" id="cadastro-opcoes">
        <div class="fdxp-shell fdxp-main-shell">
            <header class="fdxp-heading">
                <h2>Escolha a modalidade de cadastro</h2>
            </header>

            <div class="fdxp-grid">
                <article class="fdxp-card">
                    <div class="fdxp-card-head">
                        <span class="fdxp-card-label">Opera&ccedil;&atilde;o comercial</span>
                        <h3>Cadastrar minha loja</h3>
                        <p>Cadastre restaurante, mercado ou farm&aacute;cia para vender com estrutura integrada &agrave; opera&ccedil;&atilde;o Fox Delivery.</p>
                    </div>

                    <ul class="fdxp-checks">
                        <li>Receba pedidos online com gest&atilde;o centralizada</li>
                        <li>Amplie a visibilidade da sua opera&ccedil;&atilde;o</li>
                        <li>Gerencie pedidos e atendimento pelo painel</li>
                    </ul>

                    <a class="fdxp-button" href="./cadastro-loja.php">Cadastrar loja</a>
                </article>

                <article class="fdxp-card fdxp-card-rider">
                    <div class="fdxp-card-head">
                        <span class="fdxp-card-label rider">Opera&ccedil;&atilde;o log&iacute;stica</span>
                        <h3>Quero ser entregador</h3>
                        <p>Atue como parceiro log&iacute;stico com credenciamento organizado e integra&ccedil;&atilde;o &agrave; opera&ccedil;&atilde;o Fox Delivery.</p>
                    </div>

                    <ul class="fdxp-checks">
                        <li>Atue com remunera&ccedil;&atilde;o por entrega</li>
                        <li>Tenha flexibilidade operacional na rotina</li>
                        <li>Utilize o app oficial de entregador</li>
                    </ul>

                    <a class="fdxp-button fdxp-button-rider" href="./cadastro-entregador.php">Cadastrar entregador</a>
                </article>
            </div>

            <article class="fdxp-integration">
                <div class="fdxp-integration-icon" aria-hidden="true">
                    <span></span>
                </div>

                <div class="fdxp-integration-copy">
                    <h3>Integra&ccedil;&atilde;o com o painel Fox Delivery</h3>
                    <p>Cadastros seguem o fluxo oficial da plataforma, com sincroniza&ccedil;&atilde;o administrativa e acompanhamento centralizado da opera&ccedil;&atilde;o.</p>
                </div>
            </article>
        </div>
    </section>

    <footer class="fdxp-footer">
        <div class="fdxp-shell fdxp-footer-shell">
            <div class="fdxp-footer-copy">
                <strong>Fox Delivery</strong>
                <p>Cadastro oficial de parceiros para opera&ccedil;&otilde;es comerciais e log&iacute;sticas, com jornada integrada ao painel administrativo da plataforma.</p>
            </div>

            <nav class="fdxp-footer-links" aria-label="Links institucionais da Fox Delivery">
                <a href="./index.php">In&iacute;cio</a>
                <a href="./sobre.php">Sobre n&oacute;s</a>
                <a href="./index.php#blog">Blog</a>
                <a href="./index.php#apps">Aplicativos</a>
                <a href="<?= e(sixammart_url('login')) ?>">Painel administrativo</a>
            </nav>
        </div>
    </footer>
</section>
<?php

$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Parceiros';
$current = 'partners';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
