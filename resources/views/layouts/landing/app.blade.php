<!DOCTYPE html>
@php
    $landing_site_direction = session()->get('landing_site_direction');
    $country = \App\CentralLogics\Helpers::get_business_settings('country');
    $countryCode = strtolower($country ?? 'auto');
    $metaData = \App\Models\DataSetting::where('type', 'admin_landing_page')
        ->whereIn('key', ['meta_title', 'meta_description', 'meta_image'])
        ->get()
        ->keyBy('key') ?? [];

    $fixed_link = \App\Models\DataSetting::where(['key' => 'fixed_link', 'type' => 'admin_landing_page'])->first();
    $fixed_link = isset($fixed_link->value) ? json_decode($fixed_link->value, true) : null;

    $toggle_store_registration = \App\CentralLogics\Helpers::get_business_settings('toggle_store_registration');
    $toggle_dm_registration = \App\CentralLogics\Helpers::get_business_settings('toggle_dm_registration');

    $local = session()->has('landing_local') ? session('landing_local') : null;
    $lang = \App\CentralLogics\Helpers::get_business_settings('system_language');
@endphp
<html dir="{{ $landing_site_direction }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title')</title>
    @include('layouts.landing._seo')

    <link rel="stylesheet" href="{{ asset('public/assets/landing/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('public/assets/landing/css/customize-animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('public/assets/landing/css/odometer.css') }}" />
    <link rel="stylesheet" href="{{ asset('public/assets/landing/css/owl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('public/assets/admin/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ asset('public/assets/landing/css/main.css') }}"/>
    <link rel="stylesheet" href="{{asset('public/assets/admin/intltelinput/css/intlTelInput.css')}}">
    <link rel="icon" type="image/x-icon" href="{{\App\CentralLogics\Helpers::iconFullUrl()}}">
    @stack('css_or_js')
</head>
<body>
    @php($hideLandingChrome = $hideLandingChrome ?? false)

    <div id="landing-loader"></div>

    @if(!$hideLandingChrome)
    <header class="fox-header fox-header-overlay">
        <div class="container">
            <div class="navbar-bottom-wrapper">
                <a href="{{ route('home') }}" class="logo fox-logo">
                    <img class="onerror-image" data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}" src="{{ \App\CentralLogics\Helpers::logoFullUrl() }}" alt="Fox Delivery">
                </a>

                <ul class="menu fox-menu">
                    <li><a href="{{ route('restaurant.create') }}" class="{{ Request::is('restaurant*') ? 'active' : '' }}">Menu parceiro</a></li>
                    <li><a href="{{ route('contact-us') }}">Blog</a></li>
                    <li><a href="{{ route('about-us') }}">Sobre nós</a></li>
                    <li><a href="#baixar-app">Baixar App</a></li>
                </ul>

                <div class="nav-toggle d-lg-none ms-auto me-3"><span></span><span></span><span></span></div>

                <div class="fox-header-actions">
                    @if ($lang)
                        <div class="dropdown--btn-hover position-relative">
                            <a class="dropdown--btn border-0 px-3 header--btn text-uppercase d-flex align-items-center" href="javascript:void(0)">
                                @foreach($lang as $data)
                                    @if($data['code']==$local)
                                        <span class="me-1">{{$data['code']}}</span>
                                    @elseif(!$local && $data['default'] == true)
                                        <span class="me-1">{{$data['code']}}</span>
                                    @endif
                                @endforeach
                            </a>
                            <ul class="dropdown-list py-0" style="min-width:120px; top:100%">
                                @foreach($lang as $data)
                                    @if($data['status']==1)
                                        <li class="py-0"><a href="{{route('lang',[$data['code']])}}">{{$data['code']}}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($toggle_store_registration || $toggle_dm_registration)
                        <a href="{{ $toggle_store_registration ? route('restaurant.create') : route('deliveryman.create') }}" class="fox-btn fox-btn-light fox-btn-sm">Cadastrar</a>
                    @endif
                    @if ($fixed_link && !empty($fixed_link['web_app_url_status']))
                        <a href="{{ $fixed_link['web_app_url'] }}" target="_blank" class="fox-btn fox-btn-primary fox-btn-sm">Entrar</a>
                    @endif
                </div>
            </div>
        </div>
    </header>
    @endif

    @yield('content')

    @if(!$hideLandingChrome)
    <footer class="fox-footer">
        <div class="container">
            <div class="fox-footer-top">
                <img class="onerror-image" data-onerror-image="{{ asset('public/assets/admin/img/160x160/img2.jpg') }}" src="{{\App\CentralLogics\Helpers::logoFullUrl()}}" alt="Fox Delivery">
                <p>{{ \App\CentralLogics\Helpers::get_settings('footer_text') }}</p>
            </div>
            <div class="fox-footer-bottom">
                <span>{{ \App\CentralLogics\Helpers::get_settings('address') }}</span>
                <span>{{ \App\CentralLogics\Helpers::get_settings('phone') }}</span>
                <span>{{ \App\CentralLogics\Helpers::get_settings('email_address') }}</span>
            </div>
        </div>
    </footer>
    @endif

    <script src="{{asset('public/assets/landing/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('public/assets/landing/js/viewport.jquery.js')}}"></script>
    <script src="{{asset('public/assets/landing/js/wow.min.js')}}"></script>
    <script src="{{asset('public/assets/landing/js/select2.min.js')}}"></script>
    <script src="{{asset('public/assets/landing/js/owl.min.js')}}"></script>
    <script src="{{asset('public/assets/landing/js/odometer.min.js')}}"></script>
    <script src="{{asset('public/assets/landing/js/main.js')}}"></script>
    <script src="{{asset('public/assets/admin/js/toastr.js')}}"></script>
    <script src="{{asset('public/assets/admin/intltelinput/js/intlTelInput.min.js')}}"></script>

    {!! Toastr::message() !!}
    @if ($errors->any())
        <script>
            @foreach($errors->all() as $error)
            toastr.error('{{$error}}', Error, {CloseButton: true, ProgressBar: true});
            @endforeach
        </script>
    @endif
    @stack('script_2')

    <script>
        "use strict";
        const inputs = document.querySelectorAll('input[type="tel"]');
        inputs.forEach(input => {
            window.intlTelInput(input, {
                initialCountry: "{{$countryCode}}",
                utilsScript: "{{ asset('public/assets/admin/intltelinput/js/utils.js') }}",
                autoInsertDialCode: true,
                nationalMode: false,
                formatOnDisplay: false,
            });
        });
    </script>
</body>
</html>
