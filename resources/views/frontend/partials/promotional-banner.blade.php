@php
    $lang = selectedLang();
    $promotional_banner_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::PROMOTIONAL_BANNER);
    $promotional_banner = App\Models\Admin\SiteSections::getData( $promotional_banner_slug)->first();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Promotional Banner section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="promo-main-section">
    <div class="promo-slider">
        <div class="swiper-wrapper">
            @if (isset($promotional_banner->value->items))
                @foreach ($promotional_banner->value->items ?? [] as $item)
                    @if ($item->status == true)
                        <div class="swiper-slide">
                            <div class="promotional-banner-section bg_img" data-background="{{ get_image($item->image , 'site-section') }}">
                                <div class="container">
                                    <div class="promo-btn-area">
                                        <a href="{{ @$item->button_link }}" class="btn--base">{{ @$item->language->$lang->button_name }} <i class="las la-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Promotional Banner section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->