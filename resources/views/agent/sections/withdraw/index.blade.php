@extends('agent.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__($page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none">
        <div class="col-lg-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __($page_title) }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute("agent.withdraw.insert") }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center"><span>{{ __("Exchange Rate") }}</span> <span class="rate-show">--</span></code>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Payment Gateway") }}<span>*</span></label>
                                    <select class="form--control nice-select gateway-select" name="gateway">
                                        @foreach ($payment_gateways ?? [] as $item)
                                            <option
                                                value="{{ $item->alias  }}"
                                                data-currency="{{ $item->currency_code }}"
                                                data-min_amount="{{ $item->min_limit }}"
                                                data-max_amount="{{ $item->max_limit }}"
                                                data-percent_charge="{{ $item->percent_charge }}"
                                                data-fixed_charge="{{ $item->fixed_charge }}"
                                                data-rate="{{ $item->rate }}"
                                                >
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">

                                    <label>{{ __("Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control" placeholder="Enter Amount" required name="amount" value="{{ old("amount") }}">
                                        <select class="form--control nice-select" name ="wallet_currency">
                                            @foreach($currencies ?? [] as $key => $currency)
                                            <option value="{{ $currency->code }}" data-rate="{{ $currency->rate }}">{{ $currency->code }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                    <code class="d-block mt-10 text-end text--warning balance-show">{{ __("Available Balance") }} {{ authWalletBalance() }} {{ get_default_currency_code() }}</code>
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    <div class="note-area">
                                        <code class="d-block limit-show">--</code>
                                        <code class="d-block fees-show">--</code>
                                    </div>
                                </div>
                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading">{{ __("Withdraw Money") }} <i class="fas fa-plus-circle ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __($page_title) }} {{__("Preview")}}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Entered Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="request-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="lab la-get-pocket"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Conversion Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="conversion-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Total Fees & Charges") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fees">--</span>
                                </div>
                            </div>

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="">{{ __("Will Get") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--success will-get">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="last">{{ __("Payable Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--warning last pay-in-total">--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title ">{{__("Withdraw Money Log")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('agent.transactions.index','withdraw') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('agent.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    var defualCurrency = "{{ get_default_currency_code() }}";
    var defualCurrencyRate = "{{ get_default_currency_rate() }}";
    var userBalanceRoute = "{{ setRoute('agent.wallets.balance') }}";

   $('select[name=gateway]').on('change',function(){
       getExchangeRate($(this));
       getLimit();
       getFees();
       getPreview();
       getUserBalance();
   });
   $('select[name=wallet_currency]').on('change',function(){
       getExchangeRate($(this));
       getLimit();
       getFees();
       getPreview();
       getUserBalance();
   });
   $(document).ready(function(){
       getExchangeRate();
       getLimit();
       getFees();
       getUserBalance();
   });
   $("input[name=amount]").keyup(function(){
        getFees();
        getPreview();
        getUserBalance();
   });
   function getExchangeRate(event) {
       var element = event;
       var currencyCode = acceptVar().sCurrency;
       var currencyRate = acceptVar().sCurrency_rate;
       var gateway_currency = acceptVar().currencyCode;
       var gateway_currency_rate = acceptVar().currencyRate;
       var exchange_rate = parseFloat(currencyRate) / parseFloat(gateway_currency_rate);
       $('.rate-show').html("1 " + gateway_currency + " = " + parseFloat(exchange_rate).toFixed(4) + " " + currencyCode);
   }
   function getLimit() {
       var gateway_currency = acceptVar().currencyCode;
       var gateway_currency_rate = acceptVar().currencyRate;

       var sender_currency = acceptVar().sCurrency;
       var sender_currency_rate = acceptVar().sCurrency_rate;

       var min_limit = acceptVar().currencyMinAmount;
       var max_limit =acceptVar().currencyMaxAmount;
       if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
           var min_limit_calc = parseFloat((min_limit/gateway_currency_rate) * sender_currency_rate).toFixed(2);
           var max_limit_clac = parseFloat((max_limit/gateway_currency_rate) * sender_currency_rate).toFixed(2);
           $('.limit-show').html("Limit " + min_limit_calc + " " + sender_currency + " - " + max_limit_clac + " " + sender_currency);
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
       var selectedVal                     = $("select[name=gateway] :selected");
       var currencyCode                    = $("select[name=gateway] :selected").attr("data-currency");
       var currencyRate                    = $("select[name=gateway] :selected").attr("data-rate");
       var currencyMinAmount               = $("select[name=gateway] :selected").attr("data-min_amount");
       var currencyMaxAmount               = $("select[name=gateway] :selected").attr("data-max_amount");
       var currencyFixedCharge             = $("select[name=gateway] :selected").attr("data-fixed_charge");
       var currencyPercentCharge           = $("select[name=gateway] :selected").attr("data-percent_charge");
       var senderCurrency                  = $("select[name=wallet_currency] :selected").val();;
       var senderCurrency_rate             = $("select[name=wallet_currency] :selected").attr("data-rate");;

       // var sender_select = $("input[name=from_wallet_id] :selected");

       return {
           currencyCode:currencyCode,
           currencyRate:currencyRate,
           currencyMinAmount:currencyMinAmount,
           currencyMaxAmount:currencyMaxAmount,
           currencyFixedCharge:currencyFixedCharge,
           currencyPercentCharge:currencyPercentCharge,
           selectedVal:selectedVal,
           sCurrency: senderCurrency,
           sCurrency_rate: senderCurrency_rate,

       };
   }

   function feesCalculation() {
       var sender_currency = acceptVar().currencyCode;
       var sender_currency_rate = acceptVar().currencyRate;
       var sender_amount = $("input[name=amount]").val();
       sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

       var fixed_charge = acceptVar().currencyFixedCharge;
       var percent_charge = acceptVar().currencyPercentCharge;
       if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
           // Process Calculation
           var fixed_charge_calc = parseFloat(fixed_charge);
           var percent_charge_calc = (parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
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
       var sender_currency = acceptVar().currencyCode;
       var percent = acceptVar().currencyPercentCharge;
       var charges = feesCalculation();
       if (charges == false) {
           return false;
       }
       $(".fees-show").html("Charge: " + parseFloat(charges.fixed).toFixed(2) + " " + sender_currency + " + " + parseFloat(charges.percent).toFixed(2) + "% = " + parseFloat(charges.total).toFixed(2) + " " + sender_currency);
   }
   function getPreview() {
           var senderAmount = $("input[name=amount]").val();
           var gateway_currency = acceptVar().currencyCode;
           var gateway_currency_rate = acceptVar().currencyRate;

           var sender_currency = acceptVar().sCurrency;
           var sender_currency_rate = acceptVar().sCurrency_rate;

           var exchange_rate =   parseFloat(sender_currency_rate).toFixed(16) / parseFloat(gateway_currency_rate).toFixed(16);

           senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

           // conversion Amount
           var request_amount = parseFloat(senderAmount)
           $('.request-amount').text(request_amount.toFixed(2) + " " + sender_currency);

           // conversion Amount
           var conversion_amount = parseFloat(senderAmount).toFixed(16) / parseFloat(exchange_rate).toFixed(16)
           $('.conversion-amount').text(conversion_amount.toFixed(2) + " " + gateway_currency);

           // Fees
           var charges = feesCalculation();
           // console.log(total_charge + "--");
           $('.fees').text(charges.total + " " + gateway_currency);

           // will get amount
           var willGet = parseFloat(conversion_amount).toFixed(2) - parseFloat(charges.total).toFixed(2);
           $('.will-get').text(willGet + " " + gateway_currency);

           // Pay In Total
           $('.pay-in-total').text(parseFloat(senderAmount).toFixed(2) + " " + sender_currency);

       }
       function getUserBalance() {
           var selectedCurrency = acceptVar().sCurrency;
           var CSRF = $("meta[name=csrf-token]").attr("content");
           var data = {
               _token      : CSRF,
               target      : selectedCurrency,
           };
           // Make AJAX request for getting user balance
           $.post(userBalanceRoute,data,function() {
               // success
           }).done(function(response){
               var balance = response.data;
               balance = parseFloat(balance).toFixed(2);
               $(".balance-show").html("Available Balance " + balance + " " + selectedCurrency);

           }).fail(function(response) {
               var response = JSON.parse(response.responseText);
               throwMessage(response.type,response.message.error);
           });
       }

</script>
@endpush
