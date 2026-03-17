@extends('layouts.landing.app')
@section('title', translate('messages.vendor_registration'))

@push('css_or_js')
<link rel="stylesheet" href="{{ asset('public/assets/admin/css/toastr.css') }}">
<link rel="stylesheet" href="{{ asset('public/assets/landing/css/select2.min.css') }}"/>
<style>
    .modern-register-wrap {max-width: 980px; margin: 0 auto;}
    .modern-register-card {background: #fff; border-radius: 20px; box-shadow: 0 20px 45px rgba(16,24,40,.08); overflow: hidden;}
    .modern-register-header {padding: 2rem; background: linear-gradient(135deg,#2563eb,#7c3aed); color: #fff;}
    .modern-register-body {padding: 2rem;}
    .modern-section-title {font-size: 1rem; font-weight: 700; color: #111827; margin-bottom: .9rem;}
    .modern-grid {display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 1rem;}
    .modern-full {grid-column: 1/-1;}
    .modern-register-body label {font-weight: 600; margin-bottom: .35rem;}
    .modern-register-body .form-control {border-radius: 12px; min-height: 46px;}
    @media (max-width: 767px){.modern-grid{grid-template-columns:1fr;}.modern-register-header,.modern-register-body{padding:1.25rem;}}
</style>
@endpush

@section('content')
@php($recaptcha = \App\CentralLogics\Helpers::get_business_settings('recaptcha'))
<section class="py-5">
    <div class="container modern-register-wrap">
        <form id="vendor-register-form" action="{{ route('restaurant.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modern-register-card">
                <div class="modern-register-header">
                    <h2 class="mb-1">{{ translate('messages.vendor') }} {{ translate('messages.application') }}</h2>
                    <p class="mb-0">{{ translate('messages.Create_your_store_profile_and_submit_for_admin_approval') }}</p>
                </div>

                <div class="modern-register-body">
                    <input type="hidden" name="lang[]" value="default">

                    <div class="modern-section-title">{{ translate('messages.owner_information') }}</div>
                    <div class="modern-grid mb-4">
                        <div>
                            <label>{{ translate('messages.first_name') }} *</label>
                            <input class="form-control" name="f_name" value="{{ old('f_name') }}" required>
                        </div>
                        <div>
                            <label>{{ translate('messages.last_name') }}</label>
                            <input class="form-control" name="l_name" value="{{ old('l_name') }}">
                        </div>
                        <div>
                            <label>{{ translate('messages.email') }} *</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                        </div>
                        <div>
                            <label>{{ translate('messages.phone') }} *</label>
                            <input class="form-control" name="phone" value="{{ old('phone') }}" required>
                        </div>
                        <div class="modern-full">
                            <label>{{ translate('messages.password') }} *</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>

                    <div class="modern-section-title">{{ translate('messages.store_information') }}</div>
                    <div class="modern-grid mb-4">
                        <div>
                            <label>{{ translate('messages.store_name') }} *</label>
                            <input class="form-control" name="name[]" value="{{ old('name.0') }}" required>
                        </div>
                        <div>
                            <label>{{ translate('messages.address') }} *</label>
                            <input class="form-control" name="address[]" value="{{ old('address.0') }}" required>
                        </div>
                        <div>
                            <label>{{ translate('messages.zone') }} *</label>
                            <select class="form-control" name="zone_id" id="zone_id" required>
                                <option value="">{{ translate('messages.select_zone') }}</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>{{ translate('messages.business_module') }} *</label>
                            <select class="form-control" name="module_id" id="module_id" required></select>
                        </div>
                        <div class="modern-full d-none" id="pickup-zone-group">
                            <label>{{ translate('messages.pickup_zone') }}</label>
                            <select name="pickup_zone_id[]" class="form-control" multiple>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>{{ translate('messages.latitude') }} *</label>
                            <input class="form-control" name="latitude" value="{{ old('latitude') }}" required>
                        </div>
                        <div>
                            <label>{{ translate('messages.longitude') }} *</label>
                            <input class="form-control" name="longitude" value="{{ old('longitude') }}" required>
                        </div>
                        <div>
                            <label>{{ translate('messages.min') }} *</label>
                            <input type="number" class="form-control" name="minimum_delivery_time" value="{{ old('minimum_delivery_time') }}" required>
                        </div>
                        <div>
                            <label>{{ translate('messages.max') }} *</label>
                            <input type="number" class="form-control" name="maximum_delivery_time" value="{{ old('maximum_delivery_time') }}" required>
                        </div>
                        <div class="modern-full">
                            <label>{{ translate('messages.delivery_time_type') }} *</label>
                            <select class="form-control" name="delivery_time_type" required>
                                <option value="min">{{ translate('messages.minute') }}</option>
                                <option value="hour">{{ translate('messages.hour') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="modern-section-title">{{ translate('messages.documents') }}</div>
                    <div class="modern-grid mb-4">
                        <div>
                            <label>{{ translate('messages.logo') }} *</label>
                            <input type="file" class="form-control" name="logo" accept="image/*" required>
                        </div>
                        <div>
                            <label>{{ translate('messages.cover') }}</label>
                            <input type="file" class="form-control" name="cover_photo" accept="image/*">
                        </div>
                        <div>
                            <label>{{ translate('messages.tin') }}</label>
                            <input class="form-control" name="tin" value="{{ old('tin') }}">
                        </div>
                        <div>
                            <label>{{ translate('messages.expire_date') }}</label>
                            <input type="date" class="form-control" name="tin_expire_date" value="{{ old('tin_expire_date') }}">
                        </div>
                        <div class="modern-full">
                            <label>{{ translate('messages.tax_certificate') }}</label>
                            <input type="file" class="form-control" name="tin_certificate_image">
                        </div>
                    </div>

                    @if(\App\CentralLogics\Helpers::subscription_check())
                    <div class="modern-section-title">{{ translate('messages.business_plan') }}</div>
                    <div class="modern-grid mb-4">
                        <div>
                            <label><input type="radio" name="business_plan" value="commission-base" checked> {{ translate('Commision_Base') }}</label>
                        </div>
                        <div>
                            <label><input type="radio" name="business_plan" value="subscription-base"> {{ translate('Subscription_Base') }}</label>
                        </div>
                        <div class="modern-full">
                            <label>{{ translate('messages.package') }}</label>
                            <select name="package_id" class="form-control">
                                <option value="">{{ translate('messages.select_package') }}</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->package_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif

                    @if(isset($recaptcha) && $recaptcha['status'] == 1)
                        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                    @else
                    <div class="modern-grid mb-4">
                        <div>
                            <label>{{ __('Enter recaptcha value') }} *</label>
                            <input type="text" class="form-control" name="custome_recaptcha" required value="{{ env('APP_DEBUG') ? session('six_captcha') : '' }}">
                        </div>
                        <div>
                            <label>{{ translate('messages.captcha') }}</label>
                            <div class="p-2 bg-light rounded">{!! $custome_recaptcha->inline() !!}</div>
                        </div>
                    </div>
                    @endif

                    <div class="text-end">
                        <button type="submit" class="cmn--btn border-0">{{ translate('messages.submit') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('script_2')
<script>
    (function(){
        const zoneSelect = document.getElementById('zone_id');
        const moduleSelect = document.getElementById('module_id');
        const pickupGroup = document.getElementById('pickup-zone-group');

        async function loadModules(){
            if(!zoneSelect.value){ moduleSelect.innerHTML = '<option value="">{{ translate('messages.select_zone_first') }}</option>'; return; }
            const url = `{{ route('restaurant.get-all-modules') }}?zone_id=${zoneSelect.value}&q=`;
            const res = await fetch(url);
            const data = await res.json();
            moduleSelect.innerHTML = '<option value="">{{ translate('messages.select_module') }}</option>';
            data.forEach(item => moduleSelect.insertAdjacentHTML('beforeend', `<option value="${item.id}">${item.text}</option>`));
        }

        zoneSelect.addEventListener('change', loadModules);
        moduleSelect.addEventListener('change', async function(){
            const res = await fetch(`{{ route('restaurant.get-module-type') }}?id=${this.value}`);
            const data = await res.json();
            pickupGroup.classList.toggle('d-none', data.module_type !== 'rental');
        });

        document.getElementById('vendor-register-form').addEventListener('submit', async function(e){
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            const formData = new FormData(this);
            const res = await fetch(this.action, {method: 'POST', body: formData, headers: {'X-Requested-With':'XMLHttpRequest'}});
            const data = await res.json();
            if(data.redirect_url){ window.location.href = data.redirect_url; return; }
            if(data.errors){ toastr.error(data.errors[0].message); }
            btn.disabled = false;
        });

        loadModules();
    })();
</script>
@endpush
