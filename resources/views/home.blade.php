@extends('layouts.landing.app')

@php($business_name = \App\CentralLogics\Helpers::get_business_settings('business_name'))
@section('title', (($business_name && $business_name !== 'null') ? $business_name : 'Fox Delivery') . ' | Landing')

@section('content')
    @php($landing_page_links = \App\Models\DataSetting::where(['type' => 'admin_landing_page','key' => 'download_user_app_links'])->first())
    @php($landing_page_links = isset($landing_page_links->value) ? json_decode($landing_page_links->value, true) : [])

    <main class="fox-landing">
        <section class="fox-hero" id="inicio">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <span class="fox-badge">Fox Delivery</span>
                        <h1>Tudo que você precisa entregue na sua porta</h1>
                        <p>Peça comida, mercado, farmácia e muito mais sem sair de casa.</p>
                        <div class="fox-hero-actions">
                            @if (!empty($landing_page_links['web_app_url_status']) && !empty($landing_page_links['web_app_url']))
                                <a href="{{ $landing_page_links['web_app_url'] }}" target="_blank" class="fox-btn fox-btn-primary">Peça agora</a>
                            @else
                                <a href="{{ route('home') }}" class="fox-btn fox-btn-primary">Peça agora</a>
                            @endif

                            <a href="{{ route('restaurant.create') }}" class="fox-btn fox-btn-outline">Cadastrar loja</a>
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
                            <img src="{{ asset('public/assets/landing/img/earn-money/earn-money-1.png') }}" alt="Mascote Fox Delivery" class="img-fluid">
                            <div class="fox-logo-chip">
                                <img src="{{ \App\CentralLogics\Helpers::logoFullUrl() }}" alt="Fox Delivery logo" class="onerror-image" data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="fox-section" id="categorias">
            <div class="container">
                <div class="fox-section-title">
                    <h2>Tudo em um só lugar</h2>
                    <p>Escolha a categoria ideal para sua necessidade no momento.</p>
                </div>
                <div class="fox-grid fox-grid-5">
                    @php($cats = [['🛒','Mercado'],['🍔','Restaurantes'],['💊','Farmácia'],['🛵','Entregas'],['🏪','Conveniência']])
                    @foreach($cats as $cat)
                        <article class="fox-card">
                            <div class="fox-card-icon">{{ $cat[0] }}</div>
                            <h3>{{ $cat[1] }}</h3>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="fox-section fox-how" id="como-funciona">
            <div class="container">
                <div class="fox-section-title">
                    <h2>Como funciona</h2>
                </div>
                <div class="fox-grid fox-grid-3">
                    <article class="fox-step"><span>1</span><h3>Escolha</h3><p>Selecione a loja e os produtos que deseja.</p></article>
                    <article class="fox-step"><span>2</span><h3>Peça</h3><p>Finalize o pedido com poucos cliques.</p></article>
                    <article class="fox-step"><span>3</span><h3>Receba</h3><p>Acompanhe a entrega em tempo real.</p></article>
                </div>
            </div>
        </section>

        <section class="fox-section" id="parceiros">
            <div class="container">
                <div class="fox-grid fox-grid-2">
                    <article class="fox-partner-card">
                        <h3>Restaurantes parceiros</h3>
                        <p>Expanda suas vendas com a vitrine digital da Fox Delivery.</p>
                        <a href="{{ route('restaurant.create') }}" class="fox-btn fox-btn-primary">Cadastrar restaurante</a>
                    </article>
                    <article class="fox-partner-card fox-partner-card-dark">
                        <h3>Entregadores</h3>
                        <p>Ganhe dinheiro com horários flexíveis e alta demanda de pedidos.</p>
                        <a href="{{ route('deliveryman.create') }}" class="fox-btn fox-btn-light">Quero entregar</a>
                    </article>
                </div>
            </div>
        </section>

        <section class="fox-section fox-proof" id="numeros">
            <div class="container">
                <div class="fox-grid fox-grid-3">
                    <div class="fox-counter"><strong>+10 mil</strong><span>entregadores</span></div>
                    <div class="fox-counter"><strong>+5 mil</strong><span>restaurantes</span></div>
                    <div class="fox-counter"><strong>+100 mil</strong><span>pedidos</span></div>
                </div>
            </div>
        </section>

        <section class="fox-section fox-app" id="baixar-app">
            <div class="container">
                <div class="fox-section-title">
                    <h2>Baixe o app</h2>
                    <p>Peça pelo celular com praticidade e rapidez.</p>
                </div>
                <div class="fox-store-buttons justify-content-center">
                    @if (!empty($landing_page_links['apple_store_url_status']) && !empty($landing_page_links['apple_store_url']))
                        <a href="{{ $landing_page_links['apple_store_url'] }}" target="_blank"><img src="{{ asset('public/assets/landing/img/apple.svg') }}" alt="App Store"></a>
                    @endif
                    @if (!empty($landing_page_links['playstore_url_status']) && !empty($landing_page_links['playstore_url']))
                        <a href="{{ $landing_page_links['playstore_url'] }}" target="_blank"><img src="{{ asset('public/assets/landing/img/google.svg') }}" alt="Google Play"></a>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection
