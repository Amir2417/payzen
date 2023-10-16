@extends('user.layouts.user_auth')

@php
    $lang = selectedLang();
    $auth_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::AUTH_SECTION);
    $auth_text = App\Models\Admin\SiteSections::getData( $auth_slug)->first();
@endphp


@section('content')

<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="account-section login">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-5 col-md-12">
                <div class="account-wrapper">
                    <div class="account-thumb">
                        <img src="{{ asset('public/frontend/images/account/account.jpg') }}" alt="element">
                    </div>
                    <div class="account-form-area">
                        <div class="account-logo text-center">
                            <a href="{{ setRoute('index') }}" class="site-logo site-title theme-change">
                                <img src="{{ get_logo($basic_settings) }}" white-img="{{ get_logo($basic_settings) }}"
                                dark-img="{{ get_logo($basic_settings,'dark') }}" alt="logo">
                            </a>
                        </div>
                        <h4 class="title">{{ __("Forget Password?") }}</h4>
                        <p>{{ __("Enter your email and we'll send you a otp to reset your password.") }}</p>
                        <form action="{{ setRoute('user.password.forgot.send.code') }}" class="account-form" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12 form-group">
                                    <input type="text" class="form-control form--control" name="credentials" placeholder="Enter Email" required>
                                </div>
                                <div class="col-lg-12 form-group text-center">
                                    <button type="submit" class="btn--base w-100">{{ __("Send OTP") }}</button>
                                </div>
                                <div class="col-lg-12">
                                    <div class="account-item text-center mt-10">
                                        <label>{{ __("Don't Have An Account?") }} <a href="{{ setRoute('user.login') }}" class="text--base">{{ __("Login Now") }}</a></label>
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
