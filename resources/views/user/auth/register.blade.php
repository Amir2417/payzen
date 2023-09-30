@extends('user.layouts.user_auth')

@php
    $lang = selectedLang();
    $auth_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::AUTH_SECTION);
    $auth_text = App\Models\Admin\SiteSections::getData( $auth_slug)->first();
    $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
    $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('slug',"terms-and-conditions")->where('status',1)->first();


@endphp

@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="account-section login">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-6 col-md-12">
                <div class="account-wrapper">
                    <div class="account-thumb">
                        <img src="{{ asset('public/frontend/images/account/account.jpg') }}" alt="element">
                    </div>
                    <div class="account-form-area">
                        <div class="account-logo text-center">
                            <a href="index.html" class="site-logo site-title theme-change">
                                <img src="{{ get_logo($basic_settings) }}" white-img="{{ get_logo($basic_settings) }}"
                                dark-img="{{ get_logo($basic_settings,'dark') }}" alt="logo">
                            </a>
                        </div>
                        <h4 class="title">{{ __("Register Information") }}</h4>
                        <p>{{ __(@$auth_text->value->language->$lang->register_text) }}</p>
                        <form class="account-form"action="{{ route('user.send.code') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12 form-group">
                                    <label>{{ __("Email Address") }}<span class="text--base">*</span></label>
                                    <input type="email" class="form-control form--control" name="email" placeholder="Enter Email" required value="{{ old('email') }}">
                                </div>
                                @if($basic_settings->agree_policy)
                                <div class="col-lg-12 form-group">
                                    <div class="custom-check-group">
                                        <input type="checkbox" id="agree" name="agree" required>
                                        <label for="agree">{{ __("I have agreed with") }}<a href=" {{  $policies != null? setRoute('useful.link',$policies->slug):"javascript:void(0)" }}" class="text--base">{{__("Terms Of Use & Privacy Policy")}}</a></label>
                                    </div>
                                </div>
                                @endif
                                <div class="col-lg-12 form-group text-center">
                                    <button type="submit" class="btn--base w-100">{{ __("Continue") }}</button>
                                </div>
                                <div class="col-lg-12">
                                    <div class="account-item text-center mt-10">
                                        <label>{{ __("Already Have An Account?") }}<a href="{{ setRoute('user.login') }}" class="text--base">{{ __("Login Now") }}</a></label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection

@push('script')
@endpush
