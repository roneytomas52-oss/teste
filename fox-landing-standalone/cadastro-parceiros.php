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
    font-family:"Trebuchet MS","Segoe UI",Verdana,sans-serif;
}

.fdxp-shell{
    width:min(var(--fdxp-shell),calc(100vw - 56px));
    margin:0 auto;
}

.fdxp-hero{
    position:relative;
    overflow:hidden;
    padding:30px 0 224px;
    color:#fff;
    background:
        radial-gradient(circle at 18% 18%,rgba(255,196,123,.16) 0,rgba(255,196,123,0) 24%),
        radial-gradient(circle at 82% 14%,rgba(255,241,221,.22) 0,rgba(255,241,221,0) 18%),
        radial-gradient(circle at 62% 58%,rgba(255,192,109,.12) 0,rgba(255,192,109,0) 24%),
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
    bottom:102px;
    height:226px;
    background:
        linear-gradient(180deg,rgba(255,233,205,.12) 0,rgba(255,233,205,0) 42%),
        linear-gradient(92deg,rgba(82,28,18,.7) 0%,rgba(138,57,24,.42) 30%,rgba(255,150,63,.08) 100%);
    transform:skewY(-8deg);
    opacity:.78;
}

.fdxp-topbar,
.fdxp-hero-grid{
    position:relative;
    z-index:2;
}

.fdxp-topbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    margin-bottom:66px;
}

.fdxp-brand{
    display:inline-flex;
    align-items:center;
    text-decoration:none;
}

.fdxp-brand img{
    display:block;
    width:248px;
    max-width:100%;
    max-height:74px;
    height:auto;
    filter:drop-shadow(0 14px 26px rgba(90,19,7,.16));
}

.fdxp-nav{
    display:flex;
    align-items:center;
    gap:28px;
    flex-wrap:wrap;
}

.fdxp-nav a{
    color:rgba(255,255,255,.97);
    text-decoration:none;
    font-size:17px;
    font-weight:800;
    letter-spacing:-.01em;
}

.fdxp-nav a:first-child::before{
    content:"";
    display:inline-block;
    width:12px;
    height:12px;
    margin-right:10px;
    border-radius:3px;
    background:#fff4ea;
    box-shadow:inset 0 0 0 3px rgba(228,63,19,.32);
    vertical-align:middle;
}

.fdxp-nav a:nth-child(2)::before{
    content:"";
    display:inline-block;
    width:12px;
    height:2px;
    margin-right:10px;
    background:#fff4ea;
    box-shadow:0 5px 0 #fff4ea,0 -5px 0 #fff4ea;
    vertical-align:middle;
}

.fdxp-actions{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.fdxp-pill{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:144px;
    padding:14px 24px;
    border-radius:999px;
    background:linear-gradient(180deg,#ff6921,#de2715);
    border:1px solid rgba(255,255,255,.18);
    box-shadow:0 18px 30px rgba(120,20,8,.2);
    color:#fff;
    text-decoration:none;
    font-size:18px;
    font-weight:900;
    transition:transform .2s ease,box-shadow .2s ease;
}

.fdxp-pill:hover,
.fdxp-button:hover{
    transform:translateY(-1px);
}

.fdxp-pill-soft{
    background:rgba(230,75,28,.16);
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
    gap:34px;
    align-items:end;
    min-height:640px;
}

.fdxp-copy{
    max-width:500px;
    padding:42px 0 112px;
}

.fdxp-copy h1{
    margin:0 0 22px;
    font-size:82px;
    line-height:.93;
    letter-spacing:-.055em;
    text-shadow:0 10px 26px rgba(109,18,10,.16);
}

.fdxp-copy p{
    margin:0;
    max-width:350px;
    font-size:25px;
    line-height:1.46;
    color:rgba(255,247,241,.98);
}

.fdxp-scene{
    position:relative;
    display:flex;
    justify-content:center;
    align-items:flex-end;
    min-height:640px;
}

.fdxp-scene::before{
    content:"";
    position:absolute;
    inset:auto 6% 14% 6%;
    height:78%;
    border-radius:50%;
    background:radial-gradient(circle at center,rgba(255,237,215,.48) 0,rgba(255,237,215,.12) 42%,rgba(255,237,215,0) 74%);
}

.fdxp-scene::after{
    content:"";
    position:absolute;
    bottom:10%;
    width:72%;
    height:34px;
    border-radius:50%;
    background:rgba(91,23,12,.18);
    filter:blur(18px);
}

.fdxp-composition{
    position:relative;
    z-index:2;
    width:min(100%,586px);
    min-height:524px;
}

.fdxp-composition::before{
    content:"";
    position:absolute;
    inset:auto 0 30px auto;
    width:240px;
    height:240px;
    border-radius:50%;
    background:radial-gradient(circle at center,rgba(255,173,88,.34) 0,rgba(255,173,88,.08) 48%,rgba(255,173,88,0) 76%);
    filter:blur(8px);
}

.fdxp-composition::after{
    content:"";
    position:absolute;
    top:34px;
    right:34px;
    width:430px;
    height:440px;
    border-radius:38px;
    background:linear-gradient(180deg,rgba(255,255,255,.08),rgba(255,255,255,.02));
    border:1px solid rgba(255,255,255,.08);
    transform:rotate(-4deg);
    transform-origin:center;
    opacity:.45;
}

.fdxp-orb{
    position:absolute;
    inset:0 8% auto auto;
    width:228px;
    height:228px;
    border-radius:50%;
    background:radial-gradient(circle at 35% 35%,rgba(255,247,238,.86) 0,rgba(255,213,165,.5) 34%,rgba(255,129,55,.16) 70%,rgba(255,129,55,0) 100%);
    filter:blur(8px);
}

.fdxp-dashboard{
    position:absolute;
    top:22px;
    right:0;
    width:448px;
    padding:28px;
    border-radius:38px;
    background:
        linear-gradient(180deg,rgba(255,249,243,.2),rgba(255,245,239,.1)),
        linear-gradient(180deg,rgba(67,23,14,.22),rgba(67,23,14,.08));
    border:1px solid rgba(255,255,255,.18);
    box-shadow:0 40px 60px rgba(95,18,10,.16);
    backdrop-filter:blur(20px);
}

.fdxp-dashboard::before{
    content:"";
    position:absolute;
    inset:16px;
    border-radius:30px;
    border:1px solid rgba(255,255,255,.09);
}

.fdxp-dashboard-top{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.fdxp-chip{
    display:inline-flex;
    align-items:center;
    padding:10px 14px;
    border-radius:999px;
    background:rgba(255,248,241,.92);
    color:#ab3d1a;
    font-size:12px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.fdxp-live{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:9px 12px;
    border-radius:999px;
    background:rgba(255,255,255,.12);
    color:#fff7f2;
    font-size:12px;
    font-weight:800;
}

.fdxp-live::before{
    content:"";
    width:8px;
    height:8px;
    border-radius:50%;
    background:#ffb34b;
    box-shadow:0 0 0 6px rgba(255,179,75,.18);
}

.fdxp-dashboard-main{
    position:relative;
    display:grid;
    gap:18px;
    margin-top:20px;
}

.fdxp-hero-card{
    display:grid;
    gap:10px;
    padding:30px 28px;
    border-radius:30px;
    background:linear-gradient(180deg,rgba(255,255,255,.97),rgba(255,244,236,.9));
    box-shadow:0 20px 36px rgba(81,24,14,.1);
}

.fdxp-hero-card strong{
    color:#55291f;
    font-size:36px;
    line-height:1.02;
    letter-spacing:-.04em;
}

.fdxp-hero-card p{
    margin:0;
    color:#7a5648;
    font-size:15px;
    line-height:1.65;
}

.fdxp-lane{
    position:relative;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.fdxp-mini{
    display:grid;
    gap:8px;
    padding:22px;
    border-radius:26px;
    background:linear-gradient(180deg,rgba(255,247,241,.94),rgba(255,238,226,.84));
    box-shadow:0 18px 30px rgba(88,27,15,.09);
}

.fdxp-mini.rider{
    background:linear-gradient(180deg,rgba(255,249,245,.94),rgba(255,234,220,.82));
}

.fdxp-mini span{
    display:inline-flex;
    width:max-content;
    padding:7px 10px;
    border-radius:999px;
    background:#fff1e8;
    color:#d15523;
    font-size:11px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.fdxp-mini strong{
    color:#53271f;
    font-size:20px;
    line-height:1.18;
    letter-spacing:-.02em;
}

.fdxp-mini p{
    margin:0;
    color:#805d4f;
    font-size:14px;
    line-height:1.58;
}

.fdxp-float{
    position:absolute;
    border-radius:26px;
    padding:18px 20px;
    background:linear-gradient(180deg,rgba(255,248,242,.96),rgba(255,240,230,.9));
    box-shadow:0 20px 32px rgba(92,27,14,.11);
}

.fdxp-float-top{
    left:4px;
    top:120px;
    width:170px;
}

.fdxp-float-bottom{
    right:-2px;
    bottom:16px;
    width:190px;
}

.fdxp-float small{
    display:block;
    color:#d15723;
    font-size:11px;
    font-weight:900;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.fdxp-float strong{
    display:block;
    margin-top:8px;
    color:#54281f;
    font-size:30px;
    line-height:1;
}

.fdxp-float p{
    margin:8px 0 0;
    color:#7a594d;
    font-size:13px;
    line-height:1.5;
}

.fdxp-curve{
    position:absolute;
    inset:auto 0 0;
    z-index:2;
    height:174px;
    pointer-events:none;
}

.fdxp-curve::before{
    content:"";
    position:absolute;
    inset:34px -5% -12px;
    background:#fff8f4;
    border-radius:52% 48% 0 0 / 100% 100% 0 0;
}

.fdxp-curve::after{
    content:"";
    position:absolute;
    inset:0 0 42px;
    background:
        linear-gradient(90deg,rgba(255,92,88,.9) 0,rgba(255,92,88,.9) 12%,transparent 12%),
        linear-gradient(90deg,rgba(255,191,191,.9) 0,rgba(255,191,191,.9) 22%,transparent 22%);
    border-radius:48% 52% 0 0 / 100% 100% 0 0;
    opacity:.96;
}

.fdxp-main{
    position:relative;
    margin-top:-110px;
    padding:0 0 122px;
    background:linear-gradient(180deg,#fff8f4 0%,#fff9f6 58%,#f4d7c1 100%);
}

.fdxp-main-shell{
    position:relative;
    z-index:3;
    display:grid;
    gap:34px;
}

.fdxp-heading{
    text-align:center;
}

.fdxp-heading h2{
    margin:0;
    color:#55291f;
    font-size:62px;
    line-height:1;
    letter-spacing:-.05em;
}

.fdxp-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:24px;
}

.fdxp-card{
    position:relative;
    display:grid;
    gap:24px;
    padding:34px 32px 32px;
    border-radius:34px;
    background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(255,248,244,.97));
    border:1px solid var(--fdxp-line);
    box-shadow:var(--fdxp-shadow);
}

.fdxp-card::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    right:0;
    height:5px;
    border-radius:34px 34px 0 0;
    background:linear-gradient(90deg,#ff7a24,#ea2f17);
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
    line-height:1.08;
    letter-spacing:-.03em;
}

.fdxp-card-head p{
    margin:0;
    color:#714d41;
    font-size:16px;
    line-height:1.68;
    max-width:30ch;
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
    font-size:18px;
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
    font-size:21px;
    font-weight:900;
    letter-spacing:-.02em;
    box-shadow:0 18px 32px rgba(188,41,15,.24);
    transition:transform .2s ease,box-shadow .2s ease;
}

.fdxp-button-rider{
    background:linear-gradient(180deg,#ff8d21,#f04d18);
}

.fdxp-integration{
    display:grid;
    grid-template-columns:auto 1fr;
    gap:22px;
    align-items:center;
    max-width:1020px;
    width:100%;
    margin:8px auto 0;
    padding:28px 34px;
    border-radius:30px;
    background:rgba(255,249,245,.97);
    border:1px solid rgba(241,215,198,.98);
    box-shadow:0 20px 38px rgba(143,60,20,.1);
}

.fdxp-integration-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:82px;
    height:82px;
    border-radius:22px;
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
    font-size:30px;
    line-height:1.1;
    letter-spacing:-.03em;
}

.fdxp-integration-copy p{
    margin:0;
    color:#856356;
    font-size:17px;
    line-height:1.6;
}

@media(max-width:1100px){
    .fdxp-topbar{
        justify-content:center;
    }

    .fdxp-nav,
    .fdxp-actions{
        justify-content:center;
    }

    .fdxp-hero-grid,
    .fdxp-grid,
    .fdxp-lane,
    .fdxp-integration{
        grid-template-columns:1fr;
    }

    .fdxp-copy{
        max-width:none;
        text-align:center;
        padding-bottom:34px;
    }

    .fdxp-copy p{
        margin:0 auto;
    }

    .fdxp-scene{
        min-height:500px;
    }

    .fdxp-heading h2{
        font-size:50px;
    }
}

@media(max-width:720px){
    .fdxp-shell{
        width:min(var(--fdxp-shell),calc(100vw - 28px));
    }

    .fdxp-hero{
        padding:20px 0 148px;
    }

    .fdxp-hero::after{
        bottom:60px;
        height:152px;
    }

    .fdxp-topbar{
        margin-bottom:28px;
    }

    .fdxp-brand img{
        width:196px;
    }

    .fdxp-nav{
        gap:16px;
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
        font-size:58px;
    }

    .fdxp-copy p{
        font-size:21px;
    }

    .fdxp-scene{
        min-height:360px;
    }

    .fdxp-dashboard{
        position:relative;
        top:auto;
        right:auto;
        width:100%;
        padding:22px;
    }

    .fdxp-float{
        position:relative;
        inset:auto;
        width:auto;
    }

    .fdxp-composition{
        display:grid;
        gap:14px;
        min-height:unset;
    }

    .fdxp-curve{
        height:118px;
    }

    .fdxp-main{
        margin-top:-68px;
        padding-bottom:78px;
    }

    .fdxp-heading h2{
        font-size:40px;
    }

    .fdxp-card,
    .fdxp-integration{
        padding:22px;
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
}
</style>

<section class="fdxp-page">
    <section class="fdxp-hero">
        <div class="fdxp-shell">
            <div class="fdxp-topbar">
                <a class="fdxp-brand" href="./index.php" aria-label="Fox Delivery">
                    <img src="./assets/fox-brand.svg" alt="Fox Delivery">
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
                    <p>Escolha como deseja participar da plataforma.</p>
                </div>

                <div class="fdxp-scene" aria-hidden="true">
                    <div class="fdxp-composition">
                        <div class="fdxp-orb"></div>

                        <div class="fdxp-dashboard">
                            <div class="fdxp-dashboard-top">
                                <span class="fdxp-chip">Fox Delivery Partners</span>
                                <span class="fdxp-live">Ao vivo</span>
                            </div>

                            <div class="fdxp-dashboard-main">
                                <article class="fdxp-hero-card">
                                    <strong>Cadastro premium para lojas e entregadores</strong>
                                    <p>Jornadas separadas, visual profissional e integra&ccedil;&atilde;o direta com a opera&ccedil;&atilde;o Fox Delivery.</p>
                                </article>

                                <div class="fdxp-lane">
                                    <article class="fdxp-mini">
                                        <span>Loja parceira</span>
                                        <strong>Cat&aacute;logo, vendas e painel operacional</strong>
                                        <p>Entrada comercial organizada para neg&oacute;cios que vendem na plataforma.</p>
                                    </article>

                                    <article class="fdxp-mini rider">
                                        <span>Entregador parceiro</span>
                                        <strong>Credenciamento e fluxo operacional</strong>
                                        <p>Jornada dedicada para cadastro, valida&ccedil;&atilde;o e entrada no ecossistema.</p>
                                    </article>
                                </div>
                            </div>
                        </div>

                        <div class="fdxp-float fdxp-float-top">
                            <small>Painel</small>
                            <strong>100%</strong>
                            <p>Sincronizado com a opera&ccedil;&atilde;o administrativa.</p>
                        </div>

                        <div class="fdxp-float fdxp-float-bottom">
                            <small>Jornadas</small>
                            <strong>2</strong>
                            <p>Fluxos separados para loja e entregador, com linguagem visual consistente.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fdxp-curve"></div>
    </section>

    <section class="fdxp-main" id="cadastro-opcoes">
        <div class="fdxp-shell fdxp-main-shell">
            <header class="fdxp-heading">
                <h2>Escolha como deseja se cadastrar</h2>
            </header>

            <div class="fdxp-grid">
                <article class="fdxp-card">
                    <div class="fdxp-card-head">
                        <span class="fdxp-card-label">Loja parceira</span>
                        <h3>Cadastrar minha loja</h3>
                        <p>Cadastre seu restaurante, mercado ou farm&aacute;cia e comece a vender pela Fox Delivery.</p>
                    </div>

                    <ul class="fdxp-checks">
                        <li>Receba pedidos online</li>
                        <li>Alcance novos clientes</li>
                        <li>Gerencie pedidos pelo painel</li>
                    </ul>

                    <a class="fdxp-button" href="./cadastro-loja.php">Cadastrar loja</a>
                </article>

                <article class="fdxp-card fdxp-card-rider">
                    <div class="fdxp-card-head">
                        <span class="fdxp-card-label rider">Entregador parceiro</span>
                        <h3>Quero ser entregador</h3>
                        <p>Trabalhe fazendo entregas com hor&aacute;rios flex&iacute;veis.</p>
                    </div>

                    <ul class="fdxp-checks">
                        <li>Ganhe por entrega</li>
                        <li>Trabalhe quando quiser</li>
                        <li>Use nosso app de entregador</li>
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
                    <p>Cadastros s&atilde;o sincronizados automaticamente com o painel administrativo.</p>
                </div>
            </article>
        </div>
    </section>
</section>
<?php

$content = ob_get_clean();
$pageTitle = 'Fox Delivery - Cadastro de Parceiros';
$current = 'partners';
$hidePageHeader = true;
$hidePageFooter = true;
require __DIR__ . '/includes/layout.php';
