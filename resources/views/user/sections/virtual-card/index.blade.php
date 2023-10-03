@extends('user.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{ __("Link Card") }}</h3>
            <a href="{{ setRoute('user.virtual.card.create') }}" class="btn--base">{{ ("Add Card") }} <i class="las la-plus"></i></a>
        </div>
    </div>
    <div class="row mb-30-none">
        @if(isset($stripe_cards))
            @foreach ($stripe_cards ?? [] as $item)
            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-30">
                <div class="link-card-item">
                    <div class="title-area">
                        <div class="h5 title">Card 1</div>
                        <button class="link-card-remove-btn"><i class="fas fa-trash"></i> Remove</button>
                    </div>
                    <div class="link-card-wrapper">
                        <div class="link-card bg_img" data-background="assets/images/account/account.jpg">
                            <div class="top">
                                <h2>{{ decrypt(@$item->name) }}</h2>
                                <img src="assets/images/element/stripe.png" />
                            </div>
                            <div class="infos">
                                <div class="card-number">
                                    <p>{{ ("Card Number") }}</p>
                                    <h1>{{ decrypt(@$item->card_number) }}</h1>
                                </div>
                                <div class="bottom">
                                    <div class="infos--bottom">
                                        <section>
                                            <p>{{ __("Expiry date") }}</p>
                                            <h3>{{ decrypt(@$item->expiration_date) }}</h3>
                                        </section>
                                        <section>
                                            <p>{{ __("CVC") }}</p>
                                            <h3>{{ decrypt(@$item->cvc_code) }}</h3>
                                        </section>
                                    </div>
                                    <div>
                                        <section>
                                            <img src="assets/images/element/visa.png" class="brand" />
                                        </section>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

@endsection

@push('script')

<script>
    var defualCurrency = "{{ get_default_currency_code() }}";
    var defualCurrencyRate = "{{ get_default_currency_rate() }}";
    $('.buyCard').on('click', function () {
        var modal = $('#BuyCardModal');
        $(document).ready(function(){
           getLimit();
           getFees();
           getPreview();
       });
       $("input[name=card_amount]").keyup(function(){
            getFees();
            getPreview();
       });
       $("input[name=card_amount]").focusout(function(){
            enterLimit();
       });
       function getLimit() {
           var currencyCode = acceptVar().currencyCode;
           var currencyRate = acceptVar().currencyRate;

           var min_limit = acceptVar().currencyMinAmount;
           var max_limit =acceptVar().currencyMaxAmount;
           if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
               var min_limit_calc = parseFloat(min_limit/currencyRate).toFixed(2);
               var max_limit_clac = parseFloat(max_limit/currencyRate).toFixed(2);
               $('.limit-show').html("Limit " + min_limit_calc + " " + currencyCode + " - " + max_limit_clac + " " + currencyCode);

               return {
                   minLimit:min_limit_calc,
                   maxLimit:max_limit_clac,
               };
           }else {
               $('.limit-show').html("--");
               return {
                   minLimit:0,
                   maxLimit:0,
               };
           }
       }
       function acceptVar() {

           var currencyCode = defualCurrency;
           var currencyRate = defualCurrencyRate;
           var currencyMinAmount ="{{getAmount($cardCharge->min_limit)}}";
           var currencyMaxAmount = "{{getAmount($cardCharge->max_limit)}}";
           var currencyFixedCharge = "{{getAmount($cardCharge->fixed_charge)}}";
           var currencyPercentCharge = "{{getAmount($cardCharge->percent_charge)}}";


           return {
               currencyCode:currencyCode,
               currencyRate:currencyRate,
               currencyMinAmount:currencyMinAmount,
               currencyMaxAmount:currencyMaxAmount,
               currencyFixedCharge:currencyFixedCharge,
               currencyPercentCharge:currencyPercentCharge,


           };
       }
       function feesCalculation() {
           var currencyCode = acceptVar().currencyCode;
           var currencyRate = acceptVar().currencyRate;
           var sender_amount = $("input[name=card_amount]").val();
           sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

           var fixed_charge = acceptVar().currencyFixedCharge;
           var percent_charge = acceptVar().currencyPercentCharge;
           if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
               // Process Calculation
               var fixed_charge_calc = parseFloat(currencyRate * fixed_charge);
               var percent_charge_calc = parseFloat(currencyRate)*(parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
               var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
               total_charge = parseFloat(total_charge).toFixed(2);
               // return total_charge;
               return {
                   total: total_charge,
                   fixed: fixed_charge_calc,
                   percent: percent_charge,
               };
           } else {
               // return "--";
               return false;
           }
       }

       function getFees() {
           var currencyCode = acceptVar().currencyCode;
           var percent = acceptVar().currencyPercentCharge;
           var charges = feesCalculation();
           if (charges == false) {
               return false;
           }
           $(".fees-show").html("Fees: " + parseFloat(charges.fixed).toFixed(2) + " " + currencyCode + " + " + parseFloat(charges.percent).toFixed(2) + "% = " + parseFloat(charges.total).toFixed(2) + " " + currencyCode);
       }
       function getPreview() {
               var senderAmount = $("input[name=card_amount]").val();
               var charges = feesCalculation();
               var sender_currency = acceptVar().currencyCode;
               var sender_currency_rate = acceptVar().currencyRate;

               senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;
               // Sending Amount
               $('.request-amount').html("Card Amount: " + senderAmount + " " + sender_currency);

                 // Fees
                var charges = feesCalculation();
               var total_charge = 0;
               if(senderAmount == 0){
                   total_charge = 0;
               }else{
                   total_charge = charges.total;
               }
               $('.fees').html("Total Charge: " + total_charge + " " + sender_currency);
               var totalPay = parseFloat(senderAmount) * parseFloat(sender_currency_rate)
               var pay_in_total = 0;
               if(senderAmount == 0 ||  senderAmount == ''){
                    pay_in_total = 0;
               }else{
                    pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
               }
               $('.payable-total').html("Payable: " + pay_in_total + " " + sender_currency);

       }
       function enterLimit(){
        var min_limit = parseFloat("{{getAmount($cardCharge->min_limit)}}");
        var max_limit =parseFloat("{{getAmount($cardCharge->max_limit)}}");
        var currencyRate = acceptVar().currencyRate;
        var sender_amount = parseFloat($("input[name=card_amount]").val());

        if( sender_amount < min_limit ){
            throwMessage('error',["Please follow the mimimum limit"]);
            $('.buyBtn').attr('disabled',true)
        }else if(sender_amount > max_limit){
            throwMessage('error',["Please follow the maximum limit"]);
            $('.buyBtn').attr('disabled',true)
        }else{
            $('.buyBtn').attr('disabled',false)
        }

       }
        modal.modal('show');
    });
   $('.fundCard').on('click', function () {
       var modal = $('#FundCardModal');
       $(document).ready(function(){
           getLimit();
           getFees();
           getPreview();
    });
    $("input[name=fund_amount]").keyup(function(){
        getFees();
        getPreview();
    });
    $("input[name=fund_amount]").focusout(function(){
        enterLimit();
    });

    function getLimit() {
        var currencyCode = acceptVar().currencyCode;
        var currencyRate = acceptVar().currencyRate;

        var min_limit = acceptVar().currencyMinAmount;
        var max_limit =acceptVar().currencyMaxAmount;
        if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
            var min_limit_calc = parseFloat(min_limit/currencyRate).toFixed(2);
            var max_limit_clac = parseFloat(max_limit/currencyRate).toFixed(2);
            $('.limit-show').html("Limit " + min_limit_calc + " " + currencyCode + " - " + max_limit_clac + " " + currencyCode);

            return {
                minLimit:min_limit_calc,
                maxLimit:max_limit_clac,
            };
        }else {
            $('.limit-show').html("--");
            return {
                minLimit:0,
                maxLimit:0,
            };
        }
    }
    function acceptVar() {

        var currencyCode = defualCurrency;
        var currencyRate = defualCurrencyRate;
        var currencyMinAmount ="{{getAmount($cardCharge->min_limit)}}";
        var currencyMaxAmount = "{{getAmount($cardCharge->max_limit)}}";
        var currencyFixedCharge = "{{getAmount($cardCharge->fixed_charge)}}";
        var currencyPercentCharge = "{{getAmount($cardCharge->percent_charge)}}";


        return {
            currencyCode:currencyCode,
            currencyRate:currencyRate,
            currencyMinAmount:currencyMinAmount,
            currencyMaxAmount:currencyMaxAmount,
            currencyFixedCharge:currencyFixedCharge,
            currencyPercentCharge:currencyPercentCharge,


        };
    }
    function feesCalculation() {
        var currencyCode = acceptVar().currencyCode;
        var currencyRate = acceptVar().currencyRate;
        var sender_amount = $("input[name=fund_amount]").val();
        sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

        var fixed_charge = acceptVar().currencyFixedCharge;
        var percent_charge = acceptVar().currencyPercentCharge;
        if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
            // Process Calculation
            var fixed_charge_calc = parseFloat(currencyRate * fixed_charge);
            var percent_charge_calc = parseFloat(currencyRate)*(parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
            var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
            total_charge = parseFloat(total_charge).toFixed(2);
            // return total_charge;
            return {
                total: total_charge,
                fixed: fixed_charge_calc,
                percent: percent_charge,
            };
        } else {
            // return "--";
            return false;
        }
    }

    function getFees() {
        var currencyCode = acceptVar().currencyCode;
        var percent = acceptVar().currencyPercentCharge;
        var charges = feesCalculation();
        if (charges == false) {
            return false;
        }
        $(".fees-show").html("Fees: " + parseFloat(charges.fixed).toFixed(2) + " " + currencyCode + " + " + parseFloat(charges.percent).toFixed(2) + "% = " + parseFloat(charges.total).toFixed(2) + " " + currencyCode);
    }
    function getPreview() {
            var senderAmount = $("input[name=fund_amount]").val();
            var charges = feesCalculation();
            var sender_currency = acceptVar().currencyCode;
            var sender_currency_rate = acceptVar().currencyRate;

            senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;
            // Sending Amount
            $('.request-amount').html("Card Amount: " + senderAmount + " " + sender_currency);

                // Fees
            var charges = feesCalculation();
            var total_charge = 0;
            if(senderAmount == 0){
                total_charge = 0;
            }else{
                total_charge = charges.total;
            }
            $('.fees').html("Total Charge: " + total_charge + " " + sender_currency);
            var totalPay = parseFloat(senderAmount) * parseFloat(sender_currency_rate)
            var pay_in_total = 0;
            if(senderAmount == 0 ||  senderAmount == ''){
                pay_in_total = 0;
            }else{
                pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
            }
            $('.payable-total').html("Payable: " + pay_in_total + " " + sender_currency);

    }
    function enterLimit(){
        var min_limit = parseFloat("{{getAmount($cardCharge->min_limit)}}");
        var max_limit =parseFloat("{{getAmount($cardCharge->max_limit)}}");
        var currencyRate = acceptVar().currencyRate;
        var sender_amount = parseFloat($("input[name=fund_amount]").val());

        if( sender_amount < min_limit ){
            throwMessage('error',["Please follow the mimimum limit"]);
            $('.fundBtn').attr('disabled',true)
        }else if(sender_amount > max_limit){
            throwMessage('error',["Please follow the maximum limit"]);
            $('.fundBtn').attr('disabled',true)
        }else{
            $('.fundBtn').attr('disabled',false)
        }

    }
       modal.modal('show');
   });



</script>
@endpush
