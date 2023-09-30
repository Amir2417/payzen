@extends('frontend.layouts.master')

@php
    $lang = selectedLang();
    $contact_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::CONTACT_SECTION);
    $contact = App\Models\Admin\SiteSections::getData( $contact_slug)->first();
@endphp

@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Contact
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="contact-section pt-200 pb-120">
    <div class="container">
        <div class="row justify-content-center mb-30-none">
            <div class="col-xl-5 col-lg-5 mb-30">
                <div class="contact-widget">
                    <div class="contact-form-header">
                        <h2 class="title">Contact Information</h2>
                        <p>We’ve grown up with the internet revolution, and we know how to deliver on its</p>
                    </div>
                    <ul class="contact-item-list">
                        <li>
                            <div class="contact-info-wrapper">
                                <div class="contact-item-icon">
                                    <i class="las la-map-marked-alt"></i>
                                </div>
                                <div class="contact-item-content">
                                    <h4 class="title">{{ __("Our Location") }}</h4>
                                    <span class="sub-title">{{ __(@$contact->value->language->$lang->location) }}</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="contact-info-wrapper">
                                <div class="contact-item-icon">
                                    <i class="las la-phone-volume"></i>
                                </div>
                                <div class="contact-item-content">
                                    <h5 class="title">{{ __("Call us on") }}: +{{ __(@$contact->value->language->$lang->mobile) }}</h5>
                                    <span class="sub-title">{{ __(@$contact->value->language->$lang->office_hours) }}</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="contact-info-wrapper">
                                <div class="contact-item-icon">
                                    <i class="las la-envelope"></i>
                                </div>
                                <div class="contact-item-content">
                                    <h5 class="title">{{ __("Email us directly") }}</h5>
                                    <span class="sub-title">{{ __(@$contact->value->language->$lang->email) }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-xl-7 col-lg-7 mb-30">
                <div class="contact-form-area">
                    <div class="contact-form-header">
                        <h3 class="title">{{ __(@$contact->value->language->$lang->heading) }}</h3>
                        <p>{{ __(@$contact->value->language->$lang->sub_heading) }}</p>
                    </div>
                    <form class="contact-form" action="{{ setRoute('contact.store') }}"  method="POST">
                        @csrf
                        <div class="row justify-content-center mb-10-none">
                            <div class="col-lg-12 form-group">
                                <label>{{ __("Your Name") }} <span class="text--base">*</span></label>
                                <input type="text" name="name" class="form--control" placeholder="Enter Name...">
                            </div>
                            <div class="col-lg-12 form-group">
                                <label>{{ __("Your Email") }} <span class="text--base">*</span></label>
                                <input type="email" name="email" class="form--control"
                                    placeholder="Enter Email...">
                            </div>
                            <div class="col-lg-12 form-group">
                                <label>{{ __("Phone") }} <span class="text--base">*</span></label>
                                <input type="number" name="mobile" class="form--control"
                                    placeholder="Enter Phone...">
                            </div>
                            <div class="col-lg-12 form-group">
                                <label>{{ __("Subject") }} <span class="text--base">*</span></label>
                                <input type="text" name="subject" class="form--control"
                                    placeholder="Enter Subject...">
                            </div>
                            <div class="col-lg-12 form-group">
                                <label>{{ __("Message") }} <span class="text--base">*</span></label>
                                <textarea class="form--control" name="message" placeholder="Your Message..."></textarea>
                            </div>
                            <div class="col-lg-12 form-group">
                                <button type="submit" class="btn--base mt-10 contact-btn">{{ __("Send Message") }} <i class="las la-angle-right"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Contact
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection


@push("script")

@endpush
