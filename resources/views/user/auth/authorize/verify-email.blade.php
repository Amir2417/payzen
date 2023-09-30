@extends('user.layouts.user_auth')

@push('css')

@endpush

@section('content')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Account
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="account-section login">
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-xxl-5 col-xl-6 col-lg-7 col-md-12">
    <div class="account-wrapper">
        <div class="account-form-area text-center">
            <div class="account-logo text-center">
                <a href="{{ setRoute('index') }}" class="site-logo site-title theme-change">
                    <img src="{{ get_logo($basic_settings) }}" white-img="{{ get_logo($basic_settings) }}"
                    dark-img="{{ get_logo($basic_settings,'dark') }}" alt="logo">
                </a>
            </div>
            <h4 class="title">{{ __("Please enter the code") }}</h4>
            {{-- @dd($data->email) --}}
            <p>{{ __("We sent a 6 digit code here") }} <span class="text--base">{{ @$data->email }}</span></p>
            <form class="account-form" action="{{ setRoute('user.verify.code',$token) }}" method="POST">
                @csrf
                <div class="row ml-b-20">
                    <div class="col-lg-12 form-group">
                        <input class="otp" type="text" name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(1)'
                            maxlength=1 required>
                        <input class="otp" type="text" name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(2)'
                            maxlength=2 required>
                        <input class="otp" type="text" name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(3)'
                            maxlength=1 required>
                        <input class="otp" type="text" name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(4)'
                            maxlength=1 required>
                        <input class="otp" type="text" name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(5)'
                            maxlength=1 required>
                        <input class="otp" type="text" name="code[]" oninput='digitValidate(this)' onkeyup='tabChange(6)'
                            maxlength=1 required>
                    </div>
                    <div class="col-lg-12 form-group text-end">
                        <div class="time-area">{{ __("You can resend the code after") }} <span id="time"></span></div>
                    </div>
                    <div class="col-lg-12 form-group text-center">
                        <button type="submit" class="btn--base w-100">{{ __("Submit") }}</button>
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
    let digitValidate = function (ele) {
        ele.value = ele.value.replace(/[^0-9]/g, '');
    }

    let tabChange = function (val) {
        let ele = document.querySelectorAll('.otp');
        if (ele[val - 1].value != '') {
            ele[val].focus()
        } else if (ele[val - 1].value == '') {
            ele[val - 2].focus()
        }
    }
</script>
<script>
    function resetTime (second = 60) {
        var coundDownSec = second;
        var countDownDate = new Date();
        countDownDate.setMinutes(countDownDate.getMinutes() + 120);
        var x = setInterval(function () {  // Get today's date and time
            var now = new Date().getTime();  // Find the distance between now and the count down date
            var distance = countDownDate - now;  // Time calculations for days, hours, minutes and seconds  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * coundDownSec)) / (1000 * coundDownSec));
            var seconds = Math.floor((distance % (1000 * coundDownSec)) / 1000);  // Output the result in an element with id="time"
            document.getElementById("time").innerHTML =seconds + "s ";  // If the count down is over, write some text

            if (distance < 0 || second < 2 ) {
                // alert();
                clearInterval(x);
                // document.getElementById("time").innerHTML = "RESEND";
                document.querySelector(".time-area").innerHTML = "Didn't get the code? <a href='{{ setRoute('user.resend.code') }}' onclick='resendOtp()' class='text--danger'>Resend</a>";
            }

            second--
        }, 1000);
    }

    resetTime();
</script>


@endpush
