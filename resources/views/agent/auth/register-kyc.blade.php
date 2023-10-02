@extends('user.layouts.user_auth')

@php
    $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
    $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('slug',"terms-and-conditions")->where('status',1)->first();
@endphp

@section('content')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="account-section login">
    <div id="body-overlay" class="body-overlay"></div>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-lg-10 col-md-12">
                <div class="account-wrapper">
                    <div class="account-form-area">
                        <div class="account-logo text-center">
                            <a href="{{ setRoute('index') }}" class="site-logo site-title theme-change">
                                <img src="{{ get_logo($basic_settings) }}" white-img="{{ get_logo($basic_settings) }}"
                                dark-img="{{ get_logo($basic_settings,'dark') }}" alt="logo">
                            </a>
                        </div>
                        <h4 class="title">{{ __("KYC Form") }}</h4>
                        <p>{{ __("Please input all the fild for login to your account to get access to your dashboard.") }}</p>
                        <form class="account-form" action="{{ setRoute('user.register.submit') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                                    @include('admin.components.form.input',[
                                        'name'          => "firstname",
                                        'placeholder'   => "First Name",
                                        'value'         => old("firstname"),
                                    ])
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                                    @include('admin.components.form.input',[
                                        'name'          => "lastname",
                                        'placeholder'   => "Last Name",
                                        'value'         => old("lastname"),
                                    ])
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4">
                                    <select name="country" class="form--control country-select select2-basic" > </select>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text copytext">@</span>
                                        </div>
                                        <input type="email" name="email" class="form--control" placeholder="Email" value="{{ old('email',@$email) }}" readonly>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                                    @include('admin.components.form.input',[
                                        'name'          => "city",
                                        'placeholder'   => "City ",
                                        'value'         => old("city"),
                                    ])
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-4 form-group">
                                    @include('admin.components.form.input',[
                                        'name'          => "zip_code",
                                        'placeholder'   => "Enter Zip",
                                        'value'         => old('zip_code',auth()->user()->address->zip ?? "")
                                    ])
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <div class="input-group">
                                        <div class="input-group-text phone-code">+</div>
                                        <input class="phone-code" type="hidden" name="phone_code" value="" />
                                        <input type="text" class="form--control" placeholder="Enter Phone ..." name="phone" value="">
                                    </div>
                                </div>
                                @include('user.components.register-kyc',compact("kyc_fields"))
                                <div class="col-lg-6 form-group show_hide_password">
                                    <label>Password <span class="text--base">*</span></label>
                                    <input type="password" class="form-control form--control" name="password" placeholder="Enter Password" required>
                                    <a href="" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                                <div class="col-lg-6 form-group show_hide_password">
                                    <label>Confirm Password <span class="text--base">*</span></label>
                                    <input type="password" class="form-control form--control" name="password_confirmation" placeholder="Enter Password" required>
                                    <a href="" class="show-pass"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                                </div>
                                @if($basic_settings->agree_policy)
                                <div class="col-lg-6 form-group">
                                    <div class="custom-check-group">
                                        <input type="checkbox" id="level-1" name="agree">
                                        <label for="level-1">{{ __("I have agreed with") }} <a href="{{  $policies != null? setRoute('useful.link',$policies->slug):"javascript:void(0)" }}" class="text--base">{{ __("Terms Of Use") }} &amp; {{ __("Privacy Policy") }}</a></label>
                                    </div>
                                </div>
                                @endif
                                <div class="col-lg-12 form-group text-center">
                                    <button type="submit" class="btn--base w-100">{{ __("Register Now") }}</button>
                                </div>
                                <div class="col-lg-12">
                                    <div class="account-item text-center mt-10">
                                        <label>{{ __("Already Have An Account?") }} <a href="{{ setRoute('user.login') }}" class="text--base">{{ __("Login Now") }}</a></label>
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
    getAllCountries("{{ setRoute('global.countries') }}");
      $(document).ready(function(){
          $("select[name=country]").on('change',function(){
              var phoneCode = $("select[name=country] :selected").attr("data-mobile-code");
              placePhoneCode(phoneCode);
          });
          countrySelect(".country-select",$(".country-select").siblings(".select2"));


      });
</script>
<script>
    $(document).ready(function() {
        $(".show_hide_password .show-pass").on('click', function(event) {
            event.preventDefault();
            if($(this).parent().find("input").attr("type") == "text"){
                $(this).parent().find("input").attr('type', 'password');
                $(this).find("i").addClass( "fa-eye-slash" );
                $(this).find("i").removeClass( "fa-eye" );
            }else if($(this).parent().find("input").attr("type") == "password"){
                $(this).parent().find("input").attr('type', 'text');
                $(this).find("i").removeClass( "fa-eye-slash" );
                $(this).find("i").addClass( "fa-eye" );
            }
        });
    });
</script>

@endpush
