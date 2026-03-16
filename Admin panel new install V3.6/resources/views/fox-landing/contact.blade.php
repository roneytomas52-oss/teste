@extends('fox-landing.layout')
@section('title', 'Fox Delivery - Contato')
@section('content')
<section class="container contact-grid">
    <div>
        <h1>Entre em contato conosco</h1>
        <p>Estamos aqui para ajudar você.</p>
        <ul>
            <li>{{ $contactPhone }}</li>
            <li>{{ $contactEmail }}</li>
            <li>{{ $contactAddress }}</li>
        </ul>
    </div>
    <form method="POST" action="{{ route('fox.contact.send') }}" class="form-card">
        @csrf
        <h2>Envie uma mensagem</h2>
        <input name="name" placeholder="Nome" required>
        <input type="email" name="email" placeholder="Email" required>
        <textarea name="message" placeholder="Mensagem" required></textarea>
        <button class="btn" type="submit">Enviar mensagem</button>
    </form>
</section>
@endsection
