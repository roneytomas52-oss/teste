@extends('layouts.landing.app')
@php($hideLandingChrome = true)
@section('title', translate('messages.deliveryman_registration'))

@section('content')
<section class="m-0 py-5">
    <div class="container">
        <div class="reg-form js-validate">
            <div class="card __card mb-3">
                <div class="card-header border-0 pb-0 text-center pt-5">
                    <h5 class="card-title text-center">
                        Cadastro finalizado
                    </h5>
                </div>
                <div class="card-body p-4 pb-5">
                    <div class="register-congrats-txt text-center">
                        Obrigado! Logo um agente entrará em contato pelo número de telefone e e-mail cadastrado.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
