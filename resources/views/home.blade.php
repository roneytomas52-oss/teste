@extends('layouts.landing.app')

@php($business_name = \App\CentralLogics\Helpers::get_business_settings('business_name'))
@section('title', (($business_name && $business_name !== 'null') ? $business_name : 'Fox Delivery') . ' | Landing')

@section('content')
    @php($landing_page_links = \App\Models\DataSetting::where(['type' => 'admin_landing_page','key' => 'download_user_app_links'])->first())
    @php($landing_page_links = isset($landing_page_links->value) ? json_decode($landing_page_links->value, true) : [])

    <main class="fox-landing fox-redesign">
        <section class="fox-hero" id="inicio">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6 fox-hero-copy">
                        <h1>Tudo que você<br>precisa entregue<br>na sua porta</h1>
                        <p>Peça comida, mercado, farmácia e mais, sem sair de casa.</p>

                        <div class="fox-hero-actions">
                            @if (!empty($landing_page_links['web_app_url_status']) && !empty($landing_page_links['web_app_url']))
                                <a href="{{ $landing_page_links['web_app_url'] }}" target="_blank" class="fox-btn fox-btn-yellow">Peça agora</a>
                            @else
                                <a href="{{ route('home') }}" class="fox-btn fox-btn-yellow">Peça agora</a>
                            @endif

                            <a href="{{ route('restaurant.create') }}" class="fox-btn fox-btn-primary">Cadastrar loja</a>
                        </div>

                        <div class="fox-store-buttons">
                            @if (!empty($landing_page_links['apple_store_url_status']) && !empty($landing_page_links['apple_store_url']))
                                <a href="{{ $landing_page_links['apple_store_url'] }}" target="_blank">
                                    <img src="{{ asset('public/assets/landing/img/apple.svg') }}" alt="App Store">
                                </a>
                            @endif
                            @if (!empty($landing_page_links['playstore_url_status']) && !empty($landing_page_links['playstore_url']))
                                <a href="{{ $landing_page_links['playstore_url'] }}" target="_blank">
                                    <img src="{{ asset('public/assets/landing/img/google.svg') }}" alt="Google Play">
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="fox-hero-image">
                            <img src="{{ asset('public/assets/landing/img/earn-money/earn-money-1.png') }}" alt="Mascote Fox Delivery" class="img-fluid fox-scooter">
                        </div>
                    </div>
                </div>
            </div>
            <div class="fox-wave"></div>
        </section>

        <section class="fox-section fox-categories" id="categorias">
            <div class="container">
                <div class="fox-section-title">
                    <h2>Tudo em um só lugar</h2>
                    <p>Peça de tudo pelo celular ou computador com entrega rápida e segura.</p>
                </div>

                <div class="fox-grid fox-grid-5">
                    <article class="fox-card"><div class="fox-card-icon">🛒</div><h3>Mercado</h3></article>
                    <article class="fox-card"><div class="fox-card-icon">🍔</div><h3>Restaurantes</h3></article>
                    <article class="fox-card"><div class="fox-card-icon">💊</div><h3>Farmácia</h3></article>
                    <article class="fox-card"><div class="fox-card-icon">🛵</div><h3>Entregas</h3></article>
                    <article class="fox-card"><div class="fox-card-icon">🏪</div><h3>Conveniência</h3></article>
                </div>
            </div>
        </section>

        <section class="fox-section fox-how" id="como-funciona">
            <div class="container">
                <div class="fox-section-title">
                    <h2>Como funciona o <span>Fox Delivery</span></h2>
                </div>
                <div class="fox-grid fox-grid-3">
                    <article class="fox-step"><span>1</span><h3>Escolha</h3><p>Escolha o que precisa pelo aplicativo.</p></article>
                    <article class="fox-step"><span>2</span><h3>Peça</h3><p>Faça seu pedido com poucos cliques.</p></article>
                    <article class="fox-step"><span>3</span><h3>Receba</h3><p>Receba em minutos na sua casa.</p></article>
                </div>
                <div class="fox-download-cta" id="baixar-app">
                    <span>Baixe o aplicativo grátis!</span>
                    <div class="fox-store-buttons">
                        @if (!empty($landing_page_links['apple_store_url_status']) && !empty($landing_page_links['apple_store_url']))
                            <a href="{{ $landing_page_links['apple_store_url'] }}" target="_blank"><img src="{{ asset('public/assets/landing/img/apple.svg') }}" alt="App Store"></a>
                        @endif
                        @if (!empty($landing_page_links['playstore_url_status']) && !empty($landing_page_links['playstore_url']))
                            <a href="{{ $landing_page_links['playstore_url'] }}" target="_blank"><img src="{{ asset('public/assets/landing/img/google.svg') }}" alt="Google Play"></a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="fox-section" id="parceiros">
            <div class="container">
                <div class="fox-grid fox-grid-2">
                    <article class="fox-partner-card">
                        <h3>Seja um Restaurante Parceiro</h3>
                        <p>Cadastre seu restaurante e aumente suas vendas online.</p>
                        <a href="{{ route('restaurant.create') }}" class="fox-btn fox-btn-yellow">Cadastrar restaurante</a>
                    </article>
                    <article class="fox-partner-card fox-partner-card-dark">
                        <h3>Seja um Entregador Parceiro</h3>
                        <p>Ganhe dinheiro com horários flexíveis e alta demanda.</p>
                        <a href="{{ route('deliveryman.create') }}" class="fox-btn fox-btn-light">Cadastrar para entrega</a>
                    </article>
                </div>
            </div>
        </section>
    </main>
@endsection
