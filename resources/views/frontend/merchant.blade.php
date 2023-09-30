@extends('frontend.layouts.master')

@php
    $lang = selectedLang();
    $merchant_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::MERCHANT_SECTION);
    $merchant = App\Models\Admin\SiteSections::getData( $merchant_slug)->first();

    $merchant_app_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::MERCHANT_APP_SECTION);
    $merchant_app = App\Models\Admin\SiteSections::getData( $merchant_app_slug)->first();
@endphp

@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<div class="banner-section merchant bg_img" data-background="{{ asset("public/frontend/images/banner/banner-3.jpg") }}">
    <div class="container">
        <div class="merchant-banner-wrapper">
            <div class="row align-items-center mb-30-none">
                <div class="col-lg-6 col-md-6 mb-30">
                    <div class="banner-thumb-area text-center">
                        <img src="{{ get_image(@$merchant->value->images->banner_image,'site-section') }}" alt="banner">
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 mb-30">
                    <div class="banner-content">
                        <span class="banner-sub-titel"><i class="fas fa-qrcode"></i>{{ __(@$merchant->value->language->$lang->heading) }}</span>
                        <h1 class="banner-title">{{ __(@$merchant->value->language->$lang->sub_heading) }}</h1>
                        <p class="mb-2">{{ __(@$merchant->value->language->$lang->details) }}</p>
                        <div class="banner-btn">
                            <a href="{{ setRoute('merchant.register') }}" class="btn--base"><i class="las la-user-plus me-1"></i>{{ __("Register") }}</a>
                            <a href="{{ setRoute('merchant.login') }}" class="btn--base"><i class="las la-key me-1"></i> {{ __("Login") }}</a>
                            <a href="{{ setRoute('developer.index') }}" class="btn--base"><i class="las la-code me-1"></i>{{ __("Developer API") }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.brand-section')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start service section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.service')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End service section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection


@push("script")

@endpush
