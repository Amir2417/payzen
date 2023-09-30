   @php
    $lang = selectedLang();
    $app_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::APP_SECTION);
    $appInfo = App\Models\Admin\SiteSections::getData( $app_slug)->first();
   @endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start app section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="app-section">
  <div class="container">
      <div class="app-download-wrapper bg_img" data-background="{{ asset("public/frontend/images/element/app-bg.png") }}">
          <div class="row justify-content-center">
              <div class="col-xl-6 col-lg-8 col-md-12 text-center">
                  <h2 class="title">{{ __("Download Our app to make money transfer easy") }}</h2>
                  <div class="img-area">
                      <div class="img-wrapper">
                          <a href="{{@$appInfo->value->language->$lang->google_link }}">
                              <img src="{{ get_image(@$appInfo->value->images->google_play,'site-section') }}" alt="app">
                          </a>
                          <a href="{{@$appInfo->value->language->$lang->apple_link }}">
                              <img src="{{ get_image(@$appInfo->value->images->appple_store,'site-section') }}" alt="app">
                          </a>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  End app section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
