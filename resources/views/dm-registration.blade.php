@extends('layouts.landing.app')
@section('title', translate('messages.deliveryman_registration'))

@push('css_or_js')
<style>
    .modern-register-wrap {max-width: 980px; margin: 0 auto;}
    .modern-register-card {background: #fff; border-radius: 20px; box-shadow: 0 20px 45px rgba(16,24,40,.08); overflow: hidden;}
    .modern-register-header {padding: 2rem; background: linear-gradient(135deg,#0ea5e9,#14b8a6); color: #fff;}
    .modern-register-body {padding: 2rem;}
    .modern-grid {display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 1rem;}
    .modern-full {grid-column: 1/-1;}
    .modern-register-body .form-control {border-radius: 12px; min-height: 46px;}
    @media (max-width: 767px){.modern-grid{grid-template-columns:1fr;}.modern-register-header,.modern-register-body{padding:1.25rem;}}
</style>
@endpush

@section('content')
@php($recaptcha = \App\CentralLogics\Helpers::get_business_settings('recaptcha'))
<section class="py-5">
    <div class="container modern-register-wrap">
        <form action="{{ route('deliveryman.store') }}" method="post" enctype="multipart/form-data" class="modern-register-card">
            @csrf
            <div class="modern-register-header">
                <h2 class="mb-1">{{ translate('messages.deliveryman') }} {{ translate('messages.application') }}</h2>
                <p class="mb-0">{{ translate('messages.Fill_out_the_form_to_apply_as_a_deliveryman') }}</p>
            </div>
            <div class="modern-register-body">
                <div class="modern-grid mb-4">
                    <div><label>{{ translate('messages.first_name') }} *</label><input class="form-control" name="f_name" value="{{ old('f_name') }}" required></div>
                    <div><label>{{ translate('messages.last_name') }}</label><input class="form-control" name="l_name" value="{{ old('l_name') }}"></div>
                    <div><label>{{ translate('messages.email') }} *</label><input type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
                    <div><label>{{ translate('messages.phone') }} *</label><input class="form-control" name="phone" value="{{ old('phone') }}" required></div>

                    <div><label>{{ translate('messages.identity_type') }}</label>
                        <select class="form-control" name="identity_type">
                            <option value="passport">{{ translate('messages.passport') }}</option>
                            <option value="driving_license">{{ translate('messages.driving_license') }}</option>
                            <option value="nid">{{ translate('messages.nid') }}</option>
                        </select>
                    </div>
                    <div><label>{{ translate('messages.identity_number') }} *</label><input class="form-control" name="identity_number" value="{{ old('identity_number') }}" required></div>

                    <div><label>{{ translate('messages.zone') }} *</label>
                        <select class="form-control" name="zone_id" required>
                            <option value="">{{ translate('messages.select_zone') }}</option>
                            @foreach($zones as $zone)<option value="{{ $zone->id }}">{{ $zone->name }}</option>@endforeach
                        </select>
                    </div>
                    <div><label>{{ translate('messages.deliveryman_type') }} *</label>
                        <select class="form-control" name="earning" required>
                            <option value="1">{{ translate('messages.freelancer') }}</option>
                            <option value="0">{{ translate('messages.salary_based') }}</option>
                        </select>
                    </div>

                    <div><label>{{ translate('messages.vehicle') }} *</label>
                        <select class="form-control" name="vehicle_id" required>
                            <option value="">{{ translate('messages.select_vehicle') }}</option>
                            @foreach($vehicles as $vehicle)<option value="{{ $vehicle->id }}">{{ $vehicle->type }}</option>@endforeach
                        </select>
                    </div>
                    <div><label>{{ translate('messages.referral_code') }}</label><input class="form-control" name="referral_code" value="{{ old('referral_code') }}"></div>

                    <div class="modern-full"><label>{{ translate('messages.password') }} *</label><input type="password" class="form-control" name="password" required></div>

                    <div><label>{{ translate('messages.deliveryman_image') }}</label><input type="file" class="form-control" name="image" accept="image/*"></div>
                    <div><label>{{ translate('messages.identity_image') }}</label><input type="file" class="form-control" name="identity_image[]" accept="image/*" multiple></div>
                </div>

                @if(isset($recaptcha) && $recaptcha['status'] == 1)
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                @else
                    <div class="modern-grid mb-4">
                        <div><label>{{ __('Enter recaptcha value') }} *</label><input type="text" class="form-control" name="custome_recaptcha" required value="{{ env('APP_DEBUG') ? session('six_captcha') : '' }}"></div>
                        <div><label>{{ translate('messages.captcha') }}</label><div class="p-2 bg-light rounded"><img src="<?php echo $custome_recaptcha->inline(); ?>" style="width:100%;"></div></div>
                    </div>
                @endif

                <div class="text-end">
                    <button type="submit" class="cmn--btn border-0 outline-0">{{ translate('messages.submit') }}</button>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
