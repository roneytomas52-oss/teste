@extends('fox-landing.layout')
@section('title', 'Fox Delivery - Sobre Nós')
@section('content')
<section class="hero small">
    <div class="container">
        <h1>Sobre Nós</h1>
        <p>Transformando vidas e negócios através da entrega rápida.</p>
        <div class="cta-row">
            <a class="store" href="{{ $downloadLinks['apple_store_url'] ?? '#' }}">App Store</a>
            <a class="store" href="{{ $downloadLinks['playstore_url'] ?? '#' }}">Google Play</a>
        </div>
    </div>
</section>
@endsection
