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
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none">
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __("Recharge") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('agent.mobile.topup.confirm') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center"><span class="rate-show">--</span> <span class="fees-show">--</span> <span class="limit-show">--</span></code>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6  form-group">
                                    <label>{{ __("Topup Amount") }} <span class="text--base">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control" maxlength="20" placeholder="Enter Amount" name="sender_amount" value="{{ old("sender_amount") }}">
                                        <div class="ad-select">
                                            <div class="custom-select">
                                                <div class="custom-select-inner">
                                                    <input type="hidden" name="sender_currency">
                                                    <span class="custom-currency">--</span>
                                                </div>
                                            </div>
                                            <div class="custom-select-wrapper">
                                                <div class="custom-select-search-box">
                                                    <div class="custom-select-search-wrapper">
                                                        <button type="submit" class="search-btn"><i class="las la-search"></i></button>
                                                        <input type="text" class="form--control custom-select-search" placeholder="Search currency...">
                                                    </div>
                                                </div>
                                                <div class="custom-select-list-wrapper">
                                                    <ul class="custom-select-list">
                                                        @foreach ($sender_wallets as $item)
                                                            <li class="custom-option" data-item='{{ json_encode($item->currency) }}'>
                                                                <img src="{{ get_image($item->currency->flag,'currency-flag') }}" alt="flag" class="custom-flag">
                                                                <span class="custom-country">{{ $item->currency->name }}</span>
                                                                <span class="custom-currency">{{ $item->currency->code }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <code class="d-block mt-10 balance-show">--</code>

                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label for="topup_type">{{ __("Topup Type") }}<span>*</span></label>
                                    <select name="topup_type" id="topup_type" class="form--control nice-select">
                                        <option selected disabled>{{ __("Choose One") }}</option>
                                        @foreach ($topup_type ?? [] as $type)
                                            <option value="{{ $type->slug }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-xl-12 col-lg-12 form-group">
                                    @include('admin.components.form.input',[
                                        'label'         => "Mobile Number",
                                        'label_after'   => "<span>*</span>",
                                        'placeholder'   => "Enter Mobile Number",
                                        'name'          => "mobile_number",
                                        'value'         => old("mobile_number"),
                                    ])
                                </div>

                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading mobileTopupBtn">{{ __("Recharge Now") }} <i class="fas fa-mobile ms-1"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __("Preview") }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-wallet"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Topup Type") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span><span class="text--base topup-type">--</span></span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-wallet"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Mobile Number") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span><span class="text--base mobile-number">--</span></span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Enter Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="text--success request-amount">--</span>
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
                                    <span class="text--warning fees">--</span>
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
                                    <span class="text--info last pay-in-total">--</span>
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
            <h4 class="title ">{{__("Mobile Topup Log")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('agent.transactions.index','mobile-topup') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('agent.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
            <div class="modal-body text-center">
                <video id="preview" class="p-1 border" style="width:300px;"></video>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">@lang('close')</button>
            </div>
      </div>
    </div>
</div>
@endsection

@push('script')
<script>
    var default_currency = "{{ get_default_currency_code() }}";
    var userBalanceRoute = "{{ setRoute('agent.wallets.balance') }}";

    var fixedCharge     = "{{ $charges->fixed_charge ?? 0 }}";
    var percentCharge   = "{{ $charges->percent_charge ?? 0 }}";
    var minLimit        = "{{ $charges->min_limit ?? 0 }}";
    var maxLimit        = "{{ $charges->max_limit ?? 0 }}";

    function setAdSelectInputValue(data) {
        var data = JSON.parse(data);
        return data.code;
    }

    function adSelectActiveItem(input) {
        var adSelect        = $(input).parents(".ad-select");
        var selectedItem    = adSelect.find(".custom-option.active");
        if(selectedItem.length > 0) {
            return selectedItem.attr("data-item");
        }
        return false;
    }

    function run(selectedItem, receiver = false, userBalance = true) {
        if(selectedItem == false) {
            return false;
        }
        if(selectedItem.length == 0) {
            return false;
        }

        function acceptVar() {
            var senderCurrency                  = selectedItem.code ?? "";
            var senderCountry                   = selectedItem.name ?? "";
            var senderCurrency_rate             = selectedItem.rate ?? 0;
            var senderCurrency_minLimit         = minLimit ?? 0;
            var senderCurrency_maxLimit         = maxLimit ?? 0;
            var senderCurrency_percentCharge    = percentCharge ?? 0;
            var senderCurrency_fixedCharge      = fixedCharge ?? 0;

            var receiverCurrency                = receiver.code ? receiver.code : "";
            var receiverCountry                 = receiver.name ? receiver.name : "";
            var receiverCurrencyRate            = receiver.rate ? receiver.rate : 0;

            var topupType = $("select[name=topup_type] :selected");
            var topupName = $("select[name=topup_type] :selected").data("name");
            var mobileNumber = $("input[name=mobile_number]").val();


            return {
                sCurrency: senderCurrency,
                sCountry: senderCountry,
                sCurrency_rate: senderCurrency_rate,
                sCurrency_minLimit: senderCurrency_minLimit,
                sCurrency_maxLimit: senderCurrency_maxLimit,
                sCurrency_percentCharge: senderCurrency_percentCharge,
                sCurrency_fixedCharge: senderCurrency_fixedCharge,
                rCurrency           : receiverCurrency,
                rCountry            : receiverCountry,
                rCurrency_rate      : receiverCurrencyRate,
                topupName            :topupName,
                mobileNumber          :mobileNumber,
                topupType            :topupType,
            };
        }

        function receiveAmount() {
            var senderAmount = $("input[name=sender_amount]").val();
            var exchangeRate = getExchangeRate();

            if(senderAmount == "" || !$.isNumeric(senderAmount)) {
                senderAmount = 0;
            }

            var receiverCurrency = acceptVar().rCurrency;
            var receiveAmount = parseFloat(senderAmount) * parseFloat(exchangeRate);
            $("input[name=receiver_amount]").val(parseFloat(receiveAmount).toFixed(4));
            return receiveAmount;
        }

        function getLimit() {
            var sender_currency = acceptVar().sCurrency;
            var sender_currency_rate = acceptVar().sCurrency_rate;
            var min_limit = acceptVar().sCurrency_minLimit;
            var max_limit = acceptVar().sCurrency_maxLimit

            if($.isNumeric(min_limit) && $.isNumeric(max_limit)) {
                var min_limit_calc = parseFloat(min_limit*sender_currency_rate).toFixed(4);
                var max_limit_clac = parseFloat(max_limit*sender_currency_rate).toFixed(4);
                $('.limit-show').html("Limit: " + min_limit_calc + " " + sender_currency + " - " + max_limit_clac + " " + sender_currency);
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
        getLimit();

        function feesCalculation(){
            var sender_currency = acceptVar().sCurrency;
            var sender_currency_rate = acceptVar().sCurrency_rate;
            var sender_amount = $("input[name=sender_amount]").val();
            sender_amount == "" ? sender_amount = 0 : sender_amount = sender_amount;

            var fixed_charge = acceptVar().sCurrency_fixedCharge;
            var percent_charge = acceptVar().sCurrency_percentCharge;

            if($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
                // Process Calculation
                var fixed_charge_calc = parseFloat(sender_currency_rate*fixed_charge);
                var percent_charge_calc  = (parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
                var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
                total_charge = parseFloat(total_charge).toFixed(4);
                // return total_charge;
                return {
                    total: total_charge,
                    fixed: fixed_charge_calc,
                    percent: percent_charge_calc,
                };
            }else {
                // return "--";
                return false;
            }
        }

        function getFees() {
            var sender_currency = acceptVar().sCurrency;
            var percent = acceptVar().sCurrency_percentCharge;
            var charges = feesCalculation();
            if(charges == false) {
                return false;
            }
            $('.fees-show').html( "Fees: "+parseFloat(charges.fixed).toFixed(4) + " " + sender_currency +" + " + parseFloat(percent).toFixed(4) + "%" );
        }
        getFees();

        function getExchangeRate() {
            var sender_currency = acceptVar().sCurrency;
            var sender_currency_rate = acceptVar().sCurrency_rate;
            var rate = parseFloat(sender_currency_rate);
            $('.rate-show').html("Exchange Rate:  1 " + default_currency + " = " + parseFloat(rate).toFixed(4) + " " + sender_currency);

            return rate;
        }
        getExchangeRate();

        function getPreview() {

            var senderAmount = $("input[name=sender_amount]").val();
            var sender_currency = acceptVar().sCurrency;
            var topupName = acceptVar().topupName;
            var mobileNumber = acceptVar().mobileNumber;

            senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

            // Sending Amount
            $('.request-amount').text(senderAmount + " " + sender_currency);

            var receiver_currency = acceptVar().rCurrency;
            var receiverAmount = receiveAmount();
            receiveAmount = parseFloat(receiverAmount).toFixed(4);
            $('.receive-amount').text(receiveAmount + " " + receiver_currency);

            $(".sender-currency").text(sender_currency + " (" + acceptVar().sCountry + ")");
            $(".receiver-currency").text(receiver_currency + " (" + acceptVar().rCountry + ")");

            //bill type
            $('.topup-type').text(topupName);
           // Fees
            //bill number
            if(mobileNumber == '' || mobileNumber == 0){
                $('.mobile-number').text("Ex: 1234567891");
            }else{
                $('.mobile-number').text(mobileNumber);
            }

            // Fees
            var charges = feesCalculation();
            // console.log(total_charge + "--");
            $('.fees').text(charges.total + " " + sender_currency);

            // Pay In Total
            var pay_in_total = parseFloat(charges.total) + parseFloat(senderAmount);
            $('.pay-in-total').text(parseFloat(pay_in_total).toFixed(4) + " " + sender_currency);

        }
        getPreview();

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
                balance = parseFloat(balance).toFixed(4);
                $(".balance-show").html("Available Balance " + balance + " " + selectedCurrency);

            }).fail(function(response) {
                var response = JSON.parse(response.responseText);
                throwMessage(response.type,response.message.error);
            });
        }

        if(userBalance) {
            getUserBalance();
        }
    }

    $(document).on("click",".custom-option",function() {
        run(JSON.parse(adSelectActiveItem("input[name=sender_currency]")));
    });

    $("input[name=sender_amount]").keyup(function(){
        run(JSON.parse(adSelectActiveItem("input[name=sender_currency]")));
    });

    $("input[name=mobile_number]").keyup(function(){
        run(JSON.parse(adSelectActiveItem("input[name=sender_currency]")));
    });
    $("select[name=topup_type]").change(function(){
        run(JSON.parse(adSelectActiveItem("input[name=sender_currency]")));
    });


    $(".ad-select .custom-select-search").keyup(function(){
        var searchText = $(this).val().toLowerCase();
        var itemList =  $(this).parents(".ad-select").find(".custom-option");
        $.each(itemList,function(index,item){
            var text = $(item).find(".custom-currency").text().toLowerCase();
            var country = $(item).find(".custom-country").text().toLowerCase();

            var match = text.match(searchText);
            var countryMatch = country.match(searchText);

            if(match == null && countryMatch == null) {
                $(item).addClass("d-none");
            }else {
                $(item).removeClass("d-none");
            }
        });
    });

</script>

@endpush
