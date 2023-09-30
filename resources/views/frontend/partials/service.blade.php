@php
    $lang = selectedLang();
    $service_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::SERVICE_SECTION);
    $service = App\Models\Admin\SiteSections::getData( $service_slug)->first();
    $merchant_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::MERCHANT_SECTION);
    $merchant = App\Models\Admin\SiteSections::getData( $merchant_slug)->first();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start service section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="service-section pt-200 pb-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 text-center">
                @if( Route::currentRouteName() == 'merchant')
                <div class="section-header">
                    <span class="section-sub-titel"><i class="fas fa-qrcode"></i> {{ __(@$merchant->value->language->$lang->heading) }}</span>
                    <h2 class="section-title">{{ __(@$merchant->value->language->$lang->sub_heading) }}</h2>
                    <p>{{ __(@$merchant->value->language->$lang->details) }}</p>
                </div>
                @else
                <div class="section-header">
                    <span class="section-sub-titel"><i class="fas fa-qrcode"></i> {{ __(@$service->value->language->$lang->heading) }}</span>
                    <h2 class="section-title">{{ __(@$service->value->language->$lang->sub_heading) }}</h2>
                    <p>{{ __(@$service->value->language->$lang->details) }}</p>
                </div>
                @endif
            </div>
        </div>
        <div class="row mb-30-none">
            @if( Route::currentRouteName() == 'merchant')
                @if(isset($merchant->value->items))
                    @foreach($merchant->value->items ?? [] as $key => $item)
                        <div class="col-lg-6 col-md-6 mb-30">
                            <div class="service-item">
                                <span class="icon"><i class="{{ @$item->language->$lang->icon }}"></i></span>
                                <div class="service-content">
                                    <h4 class="title">{{ @$item->language->$lang->title }}</h4>
                                    <p>{{ @$item->language->$lang->sub_title }}</p>
                                    <div class="service-bg bg_img" data-background="{{ ("public/frontend/images/element/element-1.png") }}"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @else
            @if(isset($service->value->items))
                @foreach($service->value->items ?? [] as $key => $item)
                    <div class="col-lg-6 col-md-6 mb-30">
                        <div class="service-item">
                            <span class="icon"><i class="{{ @$item->language->$lang->icon }}"></i></span>
                            <div class="service-content">
                                <h4 class="title">{{ @$item->language->$lang->title }}</h4>
                                <p>{{ @$item->language->$lang->sub_title }}</p>
                                <div class="service-bg bg_img" data-background="{{ ("public/frontend/images/element/element-1.png") }}"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            @endif
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End service section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->