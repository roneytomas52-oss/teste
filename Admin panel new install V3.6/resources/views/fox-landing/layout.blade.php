<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Fox Delivery')</title>
    <link rel="stylesheet" href="{{ asset('public/assets/fox-landing/style.css') }}">
</head>
<body>
<header class="fox-header">
    <div class="container nav-wrap">
        <a href="{{ route('fox.home') }}" class="brand">
            <img src="{{ $businessLogo }}" alt="{{ $businessName }}">
        </a>
        <nav>
            <a href="{{ route('fox.home') }}">Início</a>
            <a href="{{ route('fox.store-registration') }}">Cadastro de Loja</a>
            <a href="{{ route('fox.delivery-registration') }}">Cadastro de Entregador</a>
            <a href="{{ route('fox.about') }}">Sobre Nós</a>
            <a href="{{ route('fox.contact') }}">Contato</a>
        </nav>
        <div class="actions">
            <a class="btn light" href="{{ route('login', ['tab' => 'admin']) }}">Entrar</a>
            <a class="btn" href="{{ route('fox.home') }}#apps">Baixar App</a>
        </div>
    </div>
</header>
<main>
    @yield('content')
</main>
<footer class="fox-footer">
    <div class="container">{{ $businessName }} · Integração 6amMart (Admin + Banco)</div>
</footer>
</body>
</html>
