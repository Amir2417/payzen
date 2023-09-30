@php
    $lang = selectedLang();
    $testimonial_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::TESTIMONIAL_SECTION);
    $testimonial = App\Models\Admin\SiteSections::getData( $testimonial_slug)->first();

@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start testimonial
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="testimonial-section ptb-120">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-7">
                <div class="section-header text-center">
                    <span class="section-sub-titel"><i class="fas fa-qrcode"></i> Testimonials</span>
                    <h2 class="section-title">What People Say About Us</h2>
                    <p>Testimonials are statements or reviews from satisfied customers or clients that demonstrate their positive experiences with business.</p>
                </div>
            </div>
        </div>
        <div class="testimonial-slider-wrapper">
            <div class="testimonial-slider">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="testimonial-item">
                            <ul class="testimonial-icon-list">
                                <li><i class="las la-star"></i></li>
                                <li><i class="las la-star"></i></li>
                                <li><i class="las la-star"></i></li>
                                <li><i class="las la-star"></i></li>
                                <li><i class="las la-star"></i></li>
                            </ul>
                            <h4 class="testimonial-title">Extraordinary Quick Solid Help</h4>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis sed illum ipsum voluptatem ea, sit, eos non ducimus ipsam provident, harum vel quasi.</p>
                            <div class="testimonial-bottom-wrapper">
                                <div class="testimonial-user-area">
                                    <div class="title-area">
                                        <h5>Fardin Mehbub</h5>
                                        <span class="testimonial-date"><i class="las la-history"></i> 18-01-2023</span>
                                    </div>
                                    <div class="user-area">
                                        <img src="assets/images/user/2.jpg" alt="user">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="testimonial-item">
                            <ul class="testimonial-icon-list">
                                <li><i class="las la-star"></i></li>
                                <li><i class="las la-star"></i></li>
                                <li><i class="las la-star"></i></li>
                                <li><i class="las la-star"></i></li>
                                <li><i class="las la-star"></i></li>
                            </ul>
                            <h4 class="testimonial-title">Extraordinary Quick Solid Help</h4>
                            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis sed illum ipsum voluptatem ea, sit, eos non ducimus ipsam provident, harum vel quasi.</p>
                            <div class="testimonial-bottom-wrapper">
                                <div class="testimonial-user-area">
                                    <div class="title-area">
                                        <h5>Fardin Mehbub</h5>
                                        <span class="testimonial-date"><i class="las la-history"></i> 18-01-2023</span>
                                    </div>
                                    <div class="user-area">
                                        <img src="assets/images/user/2.jpg" alt="user">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-nav-area">
                    <div class="slider-prev slider-nav">
                        <i class="las la-arrow-left"></i>
                    </div>
                    <div class="slider-next slider-nav">
                        <i class="las la-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End testimonial
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
