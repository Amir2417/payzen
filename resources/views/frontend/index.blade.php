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
    $slider_section_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::SLIDER_SECTION);
    $slider_section = App\Models\Admin\SiteSections::getData( $slider_section_slug)->first();
@endphp
@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Banner
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="banner-slider">
    <div class="swiper-wrapper">
        @if(isset($slider_section->value->items))
        @php
            $step = 0;
        @endphp
            @foreach ($slider_section->value->items ?? [] as $item)
            @php
                $step++;
            @endphp
            @if ($item->status == true)
                <div class="swiper-slide">
                    <div class="banner-section bg_img" data-background="{{ get_image(@$item->image,'site-section') }}">
                        <div class="container">
                            <div class="row">
                                <div class="col-xxl-6 col-xl-8 col-lg-8 col-md-10 col-sm-12">
                                    <div class="content-box">
                                        <div class="content-inner">
                                            <span class="count-text">0{{ $step }}</span>
                                            <h5>{{ @$item->language->$lang->title }}</h5>
                                            <h2>{{ @$item->language->$lang->heading }}</h2>
                                            <ul class="list clearfix">
                                                @foreach ($item->item as $data)
                                                    <li>
                                                        <div class="icon-box"><i class="{{ @$data->icon }}"></i></div>
                                                        <h3>{{ @$data->counter_value }}</h3> 
                                                        <h4>{{ @$data->item_title }}</h4>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <div class="banner-btn">
                                                <a href="{{ @$item->button_link }}" class="btn--base">{{ @$item->language->$lang->button_name }}<i class="fas fa-angle-right ms-1"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @endforeach
        @endif
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
@include('frontend.partials.promotional-banner')
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
