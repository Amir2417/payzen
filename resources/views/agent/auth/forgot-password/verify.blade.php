@extends('layouts.master')

@push('css')

@endpush

@section('content')
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="account-section login">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-5 col-md-12">
                <div class="account-wrapper">
                    <div class="account-form-area text-center">
                        <div class="account-logo text-center">
                            <a href="index.html" class="site-logo site-title theme-change">
                                <img src="{{ get_logo($basic_settings) }}" white-img="{{ get_logo($basic_settings) }}"
                                dark-img="{{ get_logo($basic_settings,'dark') }}" alt="logo">
                            </a>
                        </div>
                        <h4 class="title">Please enter the code</h4>
                        <p>We sent a 6 digit code here <span class="text--base">demo@gmail.com</span></p>
                        <form class="account-form" action="new-password.html">
                            <div class="row ml-b-20">
                                <div class="col-lg-12 form-group">
                                    <input class="otp" type="text" oninput='digitValidate(this)' onkeyup='tabChange(1)'
                                        maxlength=1 required>
                                    <input class="otp" type="text" oninput='digitValidate(this)' onkeyup='tabChange(2)'
                                        maxlength=2 required>
                                    <input class="otp" type="text" oninput='digitValidate(this)' onkeyup='tabChange(3)'
                                        maxlength=1 required>
                                    <input class="otp" type="text" oninput='digitValidate(this)' onkeyup='tabChange(4)'
                                        maxlength=1 required>
                                    <input class="otp" type="text" oninput='digitValidate(this)' onkeyup='tabChange(5)'
                                        maxlength=1 required>
                                    <input class="otp" type="text" oninput='digitValidate(this)' onkeyup='tabChange(6)'
                                        maxlength=1 required>
                                </div>
                                <div class="col-lg-12 form-group text-end">
                                    <div class="time-area">You can resend the code after <span id="time"></span></div>
                                </div>
                                <div class="col-lg-12 form-group text-center">
                                    <button type="submit" class="btn--base w-100">Submit</button>
                                </div>
                                <div class="col-lg-12 text-center">
                                    <div class="account-item">
                                        <label>Already Have An Account? <a href="login.html" class="account-control-btn">Login
                                                Now</a></label>
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
