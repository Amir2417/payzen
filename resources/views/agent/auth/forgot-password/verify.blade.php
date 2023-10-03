@extends('layouts.master')

@push('css')

@endpush

@section('content')
    <section class="account-section bg_img" data-background="{{ asset('public/frontend/images/element/account.png') }}">
        <div class="right float-end">
            <div class="account-header text-center">
                <img src="{{ get_logo($basic_settings) }}"  data-white_img="{{ get_logo($basic_settings,'white') }}"
                data-dark_img="{{ get_logo($basic_settings,'dark') }}"
                    alt="site-logo">
            </div>
            <div class="account-middle">
                <div class="account-form-area">
                    <h3 class="title">{{ __("OTP Verification") }}</h3>
                    <p>{{ __("Please check your email address to get the OTP (One time password).") }}</p>
                    <form action="{{ setRoute('merchant.password.forgot.verify.code',$token) }}" class="account-form" method="POST">
                        @csrf
                        <div class="row ml-b-20">
                            <div class="col-lg-12 form-group">
                                @include('admin.components.form.input',[
                                    'name'          => "code",
                                    'placeholder'   => "Enter Verification Code",
                                    'required'      => true,
                                    'value'         => old("code"),
                                ])
                            </div>
                            <div class="col-lg-12 form-group">
                                <div class="forgot-item">
                                    <label>{{ __("Don't get code? ") }}<a href="{{ setRoute('merchant.password.forgot.resend.code',$token) }}" class="text--base">{{ __("Resend") }}</a></label>
                                </div>
                            </div>
                            <div class="col-lg-12 form-group text-center">
                                <button type="submit" class="btn--base w-100">{{ __("Verify") }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="account-footer text-center">
                <p>{{ __("Copyright") }} © {{ date("Y",time()) }} {{ __("All Rights Reserved.") }}</a></p>
            </div>
        </div>
    </section>
@endsection

@push('script')

@endpush
