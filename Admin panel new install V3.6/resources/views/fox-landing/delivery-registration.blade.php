@extends('fox-landing.layout')
@section('title', 'Fox Delivery - Cadastro de Entregador')
@section('content')
<section class="container narrow">
    <h1>Cadastro de Entregador</h1>
    <p>Para manter 100% da sincronização com banco e painel do 6amMart, utilize o cadastro oficial do sistema.</p>
    <a class="btn" href="{{ route('deliveryman.create') }}">Abrir formulário oficial de entregador</a>
</section>
@endsection
