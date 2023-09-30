@php
    $lang = selectedLang();
    $footer_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::FOOTER_SECTION);
    $footer = App\Models\Admin\SiteSections::getData( $footer_slug)->first();
    $contact_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::CONTACT_SECTION);
    $contact = App\Models\Admin\SiteSections::getData( $contact_slug)->first();
    $app_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::APP_SECTION);
    $appInfo = App\Models\Admin\SiteSections::getData( $app_slug)->first();
    $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
    $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('status',1)->get();

@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start footer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<footer class="footer-section">
    <div class="container">
        <div class="row justify-content-center mb-30-none">
            <div class="col-xxl-3 col-xl-3 col-md-6 mb-30">
                <div class="footer-widget">
                    <div class="footer-logo">
                        <a href="{{ setRoute('index') }}" class="site-logo">
                            <img src="{{ get_logo($basic_settings) }}" alt="logo">
                        </a>
                        <p>{{ __(@$footer->value->language->$lang->details) }}</p>
                        <div class="footer-btn">
                            <a href="{{ setRoute('about') }}">{{ __("About us") }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-md-6 mb-30">
                <div class="footer-widget">
                    <h3 class="widget-title">{{ __("Newsletter") }}</h3>
                    <p class="widget-subtitle">{{ __(@$footer->value->language->$lang->newsltter_details) }}</p>
                    <div class="widget-input">
                        <form action="{{ setRoute('newsletter.submit') }}" method="POST">
                            @csrf
                            <input type="email" class="form--control" name="email" placeholder="Your mail address">
                            <button type="submit"><i class="far fa-paper-plane"></i></button>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <button type="submit"><i class="far fa-paper-plane"></i></button>
                        </form>    
                    </div>
                    <ul class="widget-social">
                        <li><a href="#0"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#0"><i class="fab fa-instagram"></i></a></li>
                        <li><a href="#0"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#0"><i class="fab fa-pinterest-p"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-md-6 mb-30">
                <div class="footer-widget">
                    <h3 class="widget-title">{{ __("Contact Us") }}</h3>
                    <ul class="widget-list">
                        <li><i class="fas fa-map-marker-alt"></i>{{ __(@$contact->value->language->$lang->location) }}</li>
                        <li><i class="fas fa-phone"></i>+{{ __(@$contact->value->language->$lang->mobile) }}</li>
                    </ul>
                    <h6 class="custom-title">{{ __(@$contact->value->language->$lang->office_hours) }}</h6>
                </div>
            </div>
            <div class="col-xxl-3 col-xl-3 col-md-6 mb-30">
                <div class="footer-widget">
                    <h3 class="widget-title">{{ __("Usefull Links") }}</h3>
                    <ul class="widget-list">
                        @foreach ($policies ?? [] as $key=> $data)
                            <li><a href="{{ setRoute('useful.link',$data->slug) }}">{{ @$data->title->language->$lang->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="copyright-area">
            <span>© 2023 <a href="#0">Payzen</a> - mobile banking. All rights reserved.</span>
        </div>
    </div>
</footer>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End footer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
