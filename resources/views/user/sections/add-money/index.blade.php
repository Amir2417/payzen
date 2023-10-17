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
            <h3 class="title">{{__("Add Money")}}</h3>
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
                        <form class="card-form" action="{{ setRoute("user.add.money.submit") }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center"><span>{{ __("Exchange Rate") }}</span> <span class="rate-show">--</span></code>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Payment Gateway") }}<span>*</span></label>
                                    <select class="form--control nice-select gateway-select" name="currency">
                                        {{-- <option disabled selected>Select Gateway</option> --}}
                                        @foreach ($payment_gateways_currencies ?? [] as $item)
                                            <option
                                                value="{{ $item->alias  }}"
                                                data-currency="{{ $item->currency_code }}"
                                                data-min_amount="{{ $item->min_limit }}"
                                                data-max_amount="{{ $item->max_limit }}"
                                                data-percent_charge="{{ $item->percent_charge }}"
                                                data-fixed_charge="{{ $item->fixed_charge }}"
                                                data-rate="{{ $item->rate }}"
                                                >
                                                {{ $item->name }} @if($item->gateway->isManual()) (Manual) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">

                                    <label>{{ __("Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control" placeholder="Enter Amount" required name="amount" value="{{ old("amount") }}">
                                        <input type="hidden" name="sender_wallet" class="sender-wallet">
                                        <select class="form--control nice-select" name="currency_code">
                                            @foreach ($user_currencies as $item)
                                                <option value="{{ $item->currency->id }}"
                                                    data-code="{{ $item->currency->code }}"
                                                    data-symbol="{{ $item->currency->symbol }}"
                                                    data-rate="{{ $item->currency->rate }}"
                                                    data-balance="{{ $item->balance }}"
                                                    data-wallet="{{ $item->id }}"
                                                    data-name="{{ $item->currency->country }}">{{ $item->currency->code }}
                                                </option>
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
                                    <button type="submit" class="btn--base w-100 btn-loading">{{ __("Add Money") }} <i class="fas fa-plus-circle ms-1"></i></button>
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
                        <h5 class="title">{{__("Add Money Preview")}}</h5>
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
                                            <span>{{ __("Conversion Amount") }}</span>
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
                                            <i class="lab la-get-pocket"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Will Get") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="will-get">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span class="last">{{ __("Total Payable Amount") }}</span>
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
            <h4 class="title">{{ __("Add Money Log") }}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('user.transactions.index','add-money') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('user.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
         var defualCurrency     = "{{ get_default_currency_code() }}";
         var defualCurrencyRate = "{{ get_default_currency_rate() }}";

        $('select[name=currency]').on('change',function(){
            getExchangeRate($(this));
            getLimit();
            getFees();
            getPreview();
        });
        $(document).ready(function(){
            getExchangeRate();
            getLimit();
            getFees();
            // getPreview();
        });
        $('select[name=currency_code]').on('change',function(){
            getExchangeRate();
            getLimit();
            getPreview();
            getFees();
            
        });
        $("input[name=amount]").keyup(function(){
             getFees();
             getPreview();
        });

        function getExchangeRate(event) {
            var element             = event;
            var currencyCode        = acceptVar().selectedCurrency;
            var selectedCurrencyRate  = acceptVar().selectedCurrencyRate;
            var selectedCurrencyCode  = acceptVar().selectedCurrency;
            var currencyMinAmount   = acceptVar().currencyMinAmount;
            var currencyMaxAmount   = acceptVar().currencyMaxAmount;
            var walletBalance       = acceptVar().walletBalance;
            var walletId            = acceptVar().walletId;
            var paymentGatewayRate  = acceptVar().currencyRate;
            var paymentGatewayCode  = acceptVar().currencyCode;

            $('.rate-show').html(parseFloat(paymentGatewayRate).toFixed(2) + " " + paymentGatewayCode + " = " + selectedCurrencyRate + " " + selectedCurrencyCode);
            $('.balance-show').html("Available Balance :" + " " + walletBalance + " " + currencyCode);
            $('.sender-wallet').val(walletId);
        }
        function getLimit() {
            var sender_currency         = acceptVar().currencyCode;
            var sender_currency_rate    = acceptVar().currencyRate;
            var min_limit               = acceptVar().currencyMinAmount;
            var max_limit               = acceptVar().currencyMaxAmount;
            if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
                var min_limit_calc = parseFloat(min_limit*sender_currency_rate).toFixed(2);
                var max_limit_clac = parseFloat(max_limit*sender_currency_rate).toFixed(2);
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
            var selectedVal             = $("select[name=currency] :selected");
            var currencyCode            = $("select[name=currency] :selected").attr("data-currency");
            var currencyRate            = $("select[name=currency] :selected").attr("data-rate");
            var currencyMinAmount       = $("select[name=currency] :selected").attr("data-min_amount");
            var currencyMaxAmount       = $("select[name=currency] :selected").attr("data-max_amount");
            var currencyFixedCharge     = $("select[name=currency] :selected").attr("data-fixed_charge");
            var currencyPercentCharge   = $("select[name=currency] :selected").attr("data-percent_charge");
            var selectedCurrency        = $("select[name=currency_code] :selected").attr("data-code");
            var selectedCurrencyRate    = $("select[name=currency_code] :selected").attr("data-rate");
            var walletBalance           = $("select[name=currency_code] :selected").attr('data-balance');
            var walletId                = $("select[name=currency_code] :selected").attr('data-wallet');

            return {
                currencyCode:currencyCode,
                currencyRate:currencyRate,
                currencyMinAmount:currencyMinAmount,
                currencyMaxAmount:currencyMaxAmount,
                currencyFixedCharge:currencyFixedCharge,
                currencyPercentCharge:currencyPercentCharge,
                selectedVal:selectedVal,
                selectedCurrency:selectedCurrency,
                selectedCurrencyRate:selectedCurrencyRate,
                walletBalance:walletBalance,
                walletId:walletId
            };
        }

        function feesCalculation() {
            var sender_currency         = acceptVar().currencyCode;
            var sender_currency_rate    = acceptVar().currencyRate;
            var currency_code           = acceptVar().selectedCurrency;
            var currance_rate           = acceptVar().selectedCurrencyRate;
            var sender_amount           = $("input[name=amount]").val();
            var request_amount          = (parseFloat(sender_amount) / parseFloat(currance_rate)) * parseFloat(sender_currency_rate);
            
            request_amount == "" ? (request_amount = 0) : (request_amount = request_amount);

            var fixed_charge = acceptVar().currencyFixedCharge;
            var percent_charge = acceptVar().currencyPercentCharge;
            if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(request_amount)) {
                // Process Calculation
                var fixed_charge_calc = parseFloat(fixed_charge) * parseFloat(sender_currency_rate);
            
                var percent_charge_calc = (parseFloat(request_amount) / 100) * parseFloat(percent_charge);
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
            var sender_currency = acceptVar().selectedCurrency;
            var percent         = acceptVar().currencyPercentCharge;
            var sender_currency_rate    = acceptVar().currencyRate;
            var selected_currency_code  = acceptVar().currencyCode;
            var charges         = feesCalculation();
            if (charges == false) {
                return false;
            }
            $(".fees-show").html("Charge: " + parseFloat(charges.fixed).toFixed(2) + " " + selected_currency_code + " + " + parseFloat(charges.percent).toFixed(2) + "%");
        }
        function getPreview() {
            var senderAmount            = $("input[name=amount]").val();
            var sender_currency         = acceptVar().selectedCurrency;
            var payment_gate_rate       = acceptVar().selectedCurrencyRate;
            var sender_currency_rate    = acceptVar().currencyRate;
            var selected_currency_code  = acceptVar().currencyCode;
            
            senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;
            var request_amount  = (parseFloat(senderAmount) / payment_gate_rate) * sender_currency_rate;

            // Sending Amount
            $('.request-amount').text((request_amount.toFixed(2)) + " " + selected_currency_code);

            // Fees
            var charges = feesCalculation();
            $('.fees').text(charges.total + " " + selected_currency_code);

            // will get amount
            
            var willGet = senderAmount;
            $('.will-get').text(willGet + " " + sender_currency);

            // Pay In Total
            var pay_in_total    = parseFloat(charges.total) + parseFloat(request_amount);
           
            
            $('.pay-in-total').text(parseFloat(pay_in_total).toFixed(2) + " " + selected_currency_code);

        }



    </script>
@endpush
