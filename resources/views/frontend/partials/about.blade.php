@php
    $lang = selectedLang();
    $about_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::ABOUT_SECTION);
    $about = App\Models\Admin\SiteSections::getData($about_slug)->first();

@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start about section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="about-section pt-200">
    <div class="container">
        <div class="row mb-30-none align-items-center">
            <div class="col-xl-6 col-lg-6 col-md-6 mb-30">
                <div class="about-thumb-area">
                    <div class="about-thumb">
                        <img src="{{ get_image(@$about->value->images->image,'site-section') }}" alt="about">
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6 mb-30">
                <div class="about-content-area">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="section-header">
                                <span class="section-sub-titel"><i class="fas fa-qrcode"></i> About Company</span>
                                <h2 class="section-title">Quick, Secure Cash Moves Installment Door With QR</h2>
                                <p>QR code support refers to the ability of a device, application, or system to recognize and read QR codes. This support can be provided through a variety of methods, such as through built-in camera software, specialized QR code scanning apps, or integrated QR code reading functionality within other applications.</p>
                                <p>In such cases, it's important to ensure that the QR code is compatible with a wide range of devices and platforms to maximize its effectiveness.</p>
                            </div>
                        </div>
                    </div>
                    <div class="about-item-wrapper">
                        <div class="about-content-item">
                            <div class="icon-area active">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="title-area">
                                <h4 class="title">Money Transfer</h4>
                                <span class="sub-title">Money transfer refers to the process of sending money from one person or entity to another</span>
                            </div>
                        </div>
                        <div class="about-content-item">
                            <div class="icon-area">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div class="title-area">
                                <h4 class="title">QR Code Support</h4>
                                <span class="sub-title">QR code support refers to the ability of a device, application, or system to recognize and read QR codes.</span>
                            </div>
                        </div>
                        <div class="about-content-item">
                            <div class="icon-area">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="title-area">
                                <h4 class="title">Tried Dependability</h4>
                                <span class="sub-title">Refers to the consistent reliability system that has been proven over time through extensive testing and use.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End about section
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
