@extends('fox-landing.layout')
@section('title', 'Fox Delivery - Cadastro de Loja')
@section('content')
<section class="container narrow">
    <h1>Cadastro de Loja</h1>
    <p>Para manter 100% da integração com painel e banco do 6amMart, o formulário oficial é usado no fluxo original do sistema.</p>
    <a class="btn" href="{{ route('restaurant.create') }}">Abrir formulário oficial de loja</a>
</section>
@endsection
