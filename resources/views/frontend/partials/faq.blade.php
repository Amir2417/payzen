@php
    $lang = selectedLang();
    $faq_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::FAQ_SECTION);
    $faq = App\Models\Admin\SiteSections::getData( $faq_slug)->first();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Faq
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="faq-section pt-200 pb-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 text-center">
                <div class="section-header">
                    <span class="section-sub-titel"><i class="fas fa-qrcode"></i>{{ __(@$faq->value->language->$lang->heading) }}</span>
                    <h2 class="section-title">{{ __(@$faq->value->language->$lang->sub_heading) }}</h2>
                    <p>{{ __(@$faq->value->language->$lang->details) }}</p>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-6">
                <div class="faq-wrapper">
                    @if(isset($faq->value->items))
                    @foreach($faq->value->items ?? [] as $key => $item)
                    <div class="faq-item">
                        <h3 class="faq-title"><span class="title">{{ __(@$item->language->$lang->question) }}</span><span class="right-icon"></span></h3>
                        <div class="faq-content">
                            <p>{{ __(@$item->language->$lang->answer) }}</p>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Faq
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
