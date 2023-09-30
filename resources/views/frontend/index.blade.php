@extends('frontend.layouts.master')

@php
    $lang = selectedLang();
    $banner_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::BANNER_SECTION);
    $banner = App\Models\Admin\SiteSections::getData( $banner_slug)->first();
    $banner_floting_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::BANNER_FLOTING);
    $banner_floting = App\Models\Admin\SiteSections::getData( $banner_floting_slug)->first();
    $service_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::SERVICE_SECTION);
    $service = App\Models\Admin\SiteSections::getData( $service_slug)->first();
    $blog_section_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::BLOG_SECTION);
    $blog_section = App\Models\Admin\SiteSections::getData( $blog_section_slug)->first();
@endphp
@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="banner-slider">
    <div class="swiper-wrapper">
        <div class="swiper-slide">
            <div class="banner-section bg_img" data-background="{{ asset("public/frontend/images/banner/banner-2.jpg") }}">
                <div class="container">
                    <div class="row">
                        <div class="col-xxl-6 col-xl-8 col-lg-8 col-md-10 col-sm-12">
                            <div class="content-box">
                                <div class="content-inner">
                                    <span class="count-text">01.</span>
                                    <h5>World Class Mobile Banking</h5>
                                    <h2>Transfer Money, Around The QRcode In A Second</h2>
                                    <ul class="list clearfix">
                                        <li>
                                           <div class="icon-box"><i class="las la-globe-americas"></i></div>
                                           <h3>80+</h3> 
                                           <h4>Available Country</h4>
                                        </li>
                                        <li>
                                           <div class="icon-box"><i class="las la-code-branch"></i></div>
                                           <h3>150+</h3> 
                                           <h4>Available Branch</h4>
                                        </li>
                                    </ul>
                                    <div class="banner-btn">
                                        <a href="index.html" class="btn--base">Read More <i class="fas fa-angle-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-slide">
            <div class="banner-section bg_img" data-background="{{ asset("public/frontend/images/banner/banner-3.jpg") }}">
                <div class="container">
                    <div class="row">
                        <div class="col-xxl-6 col-xl-8 col-lg-8 col-md-10 col-sm-12">
                            <div class="content-box">
                                <div class="content-inner">
                                    <span class="count-text">02.</span>
                                    <h5>World Class Mobile Banking</h5>
                                    <h2>Transfer Money, Around The QRcode In A Second</h2>
                                    <ul class="list clearfix">
                                        <li>
                                           <div class="icon-box"><i class="las la-globe-americas"></i></div>
                                           <h3>80+</h3> 
                                           <h4>Available Country</h4>
                                        </li>
                                        <li>
                                           <div class="icon-box"><i class="las la-code-branch"></i></div>
                                           <h3>150+</h3> 
                                           <h4>Available Branch</h4>
                                        </li>
                                    </ul>
                                    <div class="banner-btn">
                                        <a href="index.html" class="btn--base">Read More <i class="fas fa-angle-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-slide">
            <div class="banner-section bg_img" data-background="{{ asset("public/frontend/images/banner/banner-4.jpg") }}">
                <div class="container">
                    <div class="row">
                        <div class="col-xxl-6 col-xl-8 col-lg-8 col-md-10 col-sm-12">
                            <div class="content-box">
                                <div class="content-inner">
                                    <span class="count-text">03.</span>
                                    <h5>World Class Mobile Banking</h5>
                                    <h2>Transfer Money, Around The QRcode In A Second</h2>
                                    <ul class="list clearfix">
                                        <li>
                                           <div class="icon-box"><i class="las la-globe-americas"></i></div>
                                           <h3>80+</h3> 
                                           <h4>Available Country</h4>
                                        </li>
                                        <li>
                                           <div class="icon-box"><i class="las la-code-branch"></i></div>
                                           <h3>150+</h3> 
                                           <h4>Available Branch</h4>
                                        </li>
                                    </ul>
                                    <div class="banner-btn">
                                        <a href="index.html" class="btn--base">Read More <i class="fas fa-angle-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="swiper-slide">
            <div class="banner-section bg_img" data-background="{{ asset("public/frontend/images/banner/banner-5.jpg") }}">
                <div class="container">
                    <div class="row">
                        <div class="col-xxl-6 col-xl-8 col-lg-8 col-md-10 col-sm-12">
                            <div class="content-box">
                                <div class="content-inner">
                                    <span class="count-text">04.</span>
                                    <h5>World Class Mobile Banking</h5>
                                    <h2>Transfer Money, Around The QRcode In A Second</h2>
                                    <ul class="list clearfix">
                                        <li>
                                           <div class="icon-box"><i class="las la-globe-americas"></i></div>
                                           <h3>80+</h3> 
                                           <h4>Available Country</h4>
                                        </li>
                                        <li>
                                           <div class="icon-box"><i class="las la-code-branch"></i></div>
                                           <h3>150+</h3> 
                                           <h4>Available Branch</h4>
                                        </li>
                                    </ul>
                                    <div class="banner-btn">
                                        <a href="index.html" class="btn--base">Read More <i class="fas fa-angle-right ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="swiper-pagination"></div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->


<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Brand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.brand-section')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Brand
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Banner floting section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="banner-floting-section pt-120">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="banner-floting-right-area">
                    <ul class="banner-floting-right-list">
                        @if(isset($banner_floting->value->items))
                            @foreach($banner_floting->value->items ?? [] as $key => $item)
                            <li><i class="fas fa-check"></i>{{ @$item->language->$lang->name }}</li>
                            @endforeach
                        @endif
                    </ul>
                    <div class="banner-floting-right-content">
                        <h3 class="title">{{ __(@$banner_floting->value->language->$lang->title) }}</h3>
                        <p>{{ __(@$banner_floting->value->language->$lang->sub_title) }}</p>
                        <a href="{{url('/').'/'.@$banner_floting->value->language->$lang->button_link}}" class="link-area">{{ __(@$banner_floting->value->language->$lang->button_name) }} <i class="fas fa-long-arrow-alt-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Banner floting section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start how it's works section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.how-work')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End how it's works section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start map section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.map-section')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End map section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.security-section')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start why choose us section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.choose-section')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End why choose us section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.professional-banner')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start testimonial
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@include('frontend.partials.testimonials')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End testimonial
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection


@push("script")

@endpush
