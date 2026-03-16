@extends('fox-landing.layout')
@section('title', 'Fox Delivery - Início')
@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div>
            <h1>Tudo o que você precisa entregue na sua porta</h1>
            <p>Peça comida, mercado, farmácia e muito mais com padrão brasileiro e sincronização com o painel 6amMart.</p>
            <div class="cta-row">
                <a class="btn yellow" href="{{ route('fox.contact') }}">Peça agora</a>
                <a class="btn" href="{{ route('restaurant.create') }}">Cadastrar loja</a>
            </div>
            <div id="apps" class="cta-row">
                <a class="store" href="{{ $downloadLinks['apple_store_url'] ?? '#' }}">App Store</a>
                <a class="store" href="{{ $downloadLinks['playstore_url'] ?? '#' }}">Google Play</a>
            </div>
        </div>
        <div class="hero-card">🛵 Fox Delivery</div>
    </div>
</section>

<section class="cards container">
    <h2>Tudo em um só lugar</h2>
    <div class="grid5">
        <article>Mercado</article><article>Restaurantes</article><article>Farmácia</article><article>Entregas</article><article>Conveniência</article>
    </div>
</section>

<section class="how container">
    <h2>Como funciona o Fox Delivery</h2>
    <div class="grid3">
        <article><strong>Escolha</strong><p>Escolha o que precisa no app.</p></article>
        <article><strong>Peça</strong><p>Faça seu pedido em poucos cliques.</p></article>
        <article><strong>Receba</strong><p>Receba em minutos.</p></article>
    </div>
</section>

<section class="split container">
    <a class="panel" href="{{ route('restaurant.create') }}"><h3>Seja um Restaurante Parceiro</h3><p>Integração direta com cadastro de loja no 6amMart.</p></a>
    <a class="panel" href="{{ route('deliveryman.create') }}"><h3>Seja Entregador Parceiro</h3><p>Cadastro sincronizado com o painel administrativo.</p></a>
</section>
@endsection
