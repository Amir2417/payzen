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
                    <div class="account-thumb">
                        <img src="{{ asset("public/frontend/images/account/account.jpg") }}" alt="element">
                    </div>
                    <div class="account-form-area">
                        <div class="account-logo text-center">
                            <a href="index.html" class="site-logo site-title theme-change">
                                <img src="{{ get_logo($basic_settings) }}" white-img="{{ get_logo($basic_settings) }}"
                                dark-img="{{ get_logo($basic_settings,'dark') }}" alt="logo">
                            </a>
                        </div>
                        <h4 class="title">{{ __("Set New Password") }}</h4>
                        <p>{{ __("Please Enter your new password and get login access on your Dashboard.") }}</p>
                        <form action="{{ setRoute('agent.password.reset',$token) }}" class="account-form" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-lg-12 form-group show_hide_password">
                                    <label>{{ __("New Password") }}<span class="text--base">*</span></label>
                                    <input type="password" class="form-control form--control" name="password" placeholder="Enter Password" required>
                                    <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-lg-12 form-group show_hide_password">
                                    <label>{{ __("Confirm Password") }} <span class="text--base">*</span></label>
                                    <input type="password" class="form-control form--control" name="password_confirmation" placeholder="Enter Password" required>
                                    <a href="javascript:void(0)" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-lg-12 form-group text-center">
                                    <button type="submit" class="btn--base w-100">{{ __("Confirm") }}</button>
                                </div>
                                <div class="col-lg-12">
                                    <div class="account-item text-center mt-10">
                                        <label>{{ __("Don't Have An Account?") }} <a href="{{ setRoute('agent.login') }}" class="text--base">{{ __("Login Now") }}</a></label>
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
<script>
    $(document).ready(function() {
        $("#show_hide_password a").on('click', function(event) {
            event.preventDefault();
            if($('#show_hide_password input').attr("type") == "text"){
                $('#show_hide_password input').attr('type', 'password');
                $('#show_hide_password i').addClass( "fa-eye-slash" );
                $('#show_hide_password i').removeClass( "fa-eye" );
            }else if($('#show_hide_password input').attr("type") == "password"){
                $('#show_hide_password input').attr('type', 'text');
                $('#show_hide_password i').removeClass( "fa-eye-slash" );
                $('#show_hide_password i').addClass( "fa-eye" );
            }
        });
    });
</script>
@endpush
