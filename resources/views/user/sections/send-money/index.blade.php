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
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-20-none">
        <div class="col-xl-7 col-lg-7 mb-20">
            <div class="custom-card mt-10">
                <div class="dashboard-header-wrapper">
                    <h5 class="title">{{ __($page_title) }}</h5>
                </div>
                <div class="card-body">
                    <form class="card-form bounce-safe" method="POST" action="{{ setRoute('user.send.money.confirmed') }}">
                        @csrf
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 form-group text-center">
                                <div class="exchange-area">
                                    <code class="d-block mt-10 fees-show">--</code>
                                    <code class="d-block mt-10 limit-show">--</code>
                                </div>
                            </div>
                            <div class="col-xxl-12 col-xl-12 col-lg- form-group paste-wrapper">
                                <label>{{ __("Email Address") }} ({{ __("User") }})<span class="text--base">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text copytext">{{ __("Email") }}</span>
                                    </div>
                                    <input type="email" name="email" class="form--control checkUser" id="username" placeholder="Enter Email" value="{{ old('email') }}" />
                                </div>
                                <button type="button" class="paste-badge scan"  data-toggle="tooltip" title="Scan QR"><i class="fas fa-camera"></i></button>
                                <label class="exist text-start"></label>

                            </div>
                            <div class="col-xl-6 col-lg-6 form-group">
                                <label>{{ __("Request Amount") }}<span>*</span></label>
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
                                <label>{{ __("Receiver Amount") }}<span>*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form--control" readonly maxlength="20" placeholder="Enter Amount" name="receiver_amount" value="{{ old("receiver_amount") }}">
                                    <div class="ad-select">
                                        <div class="custom-select">
                                            <div class="custom-select-inner">
                                                <input type="hidden" name="receiver_currency">
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
                                                    @foreach ($receiver_wallets as $item)
                                                        <li class="custom-option" data-item='{{ json_encode($item) }}'>
                                                            <img src="{{ get_image($item->flag,'currency-flag') }}" alt="flag" class="custom-flag">
                                                            <span class="custom-country">{{ $item->name }}</span>
                                                            <span class="custom-currency">{{ $item->code }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                     
                    
                        <div class="col-xl-12 col-lg-12">
                            <button type="submit" class="btn--base w-100 btn-loading transfer">{{ __("Confirm Send") }} <i class="fas fa-paper-plane ms-1"></i></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-5 mb-20">
            <div class="custom-card mt-10">
                <div class="dashboard-header-wrapper">
                    <h5 class="title">{{ __("Summary") }}</h5>
                </div>
                <div class="card-body">
                    <div class="preview-list-wrapper">
                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="las la-wallet"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Sending From Wallet") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span><span class="text--base sender-currency">--</span></span>
                            </div>
                        </div>
                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="las la-wallet"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Receiver Wallet") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span><span class="text--base receiver-currency">--</span></span>
                            </div>
                        </div>
                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="las la-receipt"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Sending Amount") }}</span>
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
                                        <i class="las la-exchange-alt"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Exchange Rate") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span class="rate-show">--</span>
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
                                        <i class="lab la-get-pocket"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Receiver Will Get") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span class="text--danger receive-amount">--</span>
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
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h5 class="title">{{ __("Send Money Log") }}</h5>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn">
                    <a href="{{ setRoute('user.transactions.index','transfer-money') }}" class="btn--base">{{ __("View More") }}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('user.components.transaction-log',compact("transactions"))
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
    $('.scan').click(function(){
        var scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5, mirror: false });
            scanner.addListener('scan',function(content){
                var route = '{{url('user/qr/scan/')}}'+'/'+content
                $.get(route, function( data ) {
                    if(data.error){
                        throwMessage('error',[data.error]);
                    } else {
                        $("#username").val(data);
                        $("#username").focus()
                    }
                    $('#scanModal').modal('hide')
                });
            });
            Instascan.Camera.getCameras().then(function (cameras){
                if(cameras.length>0){
                    $('#scanModal').modal('show')
                        scanner.start(cameras[0]);
                } else{
                    throwMessage('error',["No camera found "]);
                }
            }).catch(function(e){
                throwMessage('error',["No camera found "]);
            });

    });

</script>
<script>
    $('.checkUser').on('keyup',function(e){
            var url = '{{ route('user.send.money.check.exist') }}';
            var value = $(this).val();
            var token = '{{ csrf_token() }}';
            if ($(this).attr('name') == 'email') {
                var data = {email:value,_token:token}

            }
            $.post(url,data,function(response) {
                if(response.own){
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').addClass('text--danger').text(response.own);
                    $('.transfer').attr('disabled',true)
                    return false
                }
                if(response['data'] != null){
                    if($('.exist').hasClass('text--danger')){
                        $('.exist').removeClass('text--danger');
                    }
                    $('.exist').text(`Valid user for transaction.`).addClass('text--success');
                    $('.transfer').attr('disabled',false)
                } else {
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').text('User doesn\'t  exists.').addClass('text--danger');
                    $('.transfer').attr('disabled',true)
                    return false
                }

            });
        });

</script>
<script>
    var default_currency = "{{ get_default_currency_code() }}";
    var userBalanceRoute = "{{ setRoute('user.wallets.balance') }}";

    var fixedCharge     = "{{ $charges->fixed_charge ?? 0 }}";
    var percentCharge   = "{{ $charges->percent_charge ?? 0 }}";
    var minLimit        = "{{ $charges->min_limit ?? 0 }}";
    var maxLimit        = "{{ $charges->max_limit ?? 0 }}";
    $(document).ready(function(){

        getLimit();
        getFees();
        getPreview();
    });
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
            var senderCurrency_minLimit         = "{{getAmount($sendMoneyCharge->min_limit)}}"
            var senderCurrency_maxLimit         = "{{getAmount($sendMoneyCharge->max_limit)}}"
            var senderCurrency_percentCharge    = "{{getAmount($sendMoneyCharge->percent_charge)}}"
            var senderCurrency_fixedCharge      = "{{getAmount($sendMoneyCharge->fixed_charge)}}"

            var receiverCurrency                = receiver.code ? receiver.code : "";
            var receiverCountry                 = receiver.name ? receiver.name : "";
            var receiverCurrencyRate            = receiver.rate ? receiver.rate : 0;

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
            $("input[name=receiver_amount]").val(parseFloat(receiveAmount).toFixed(2));
            return receiveAmount;
        }

        function getLimit() {
            var sender_currency = acceptVar().sCurrency;
            var sender_currency_rate = acceptVar().sCurrency_rate;
            var min_limit = acceptVar().sCurrency_minLimit;
            var max_limit = acceptVar().sCurrency_maxLimit

            if($.isNumeric(min_limit) && $.isNumeric(max_limit)) {
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
                total_charge = parseFloat(total_charge).toFixed(2);
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
            $('.fees-show').html("Charge: " + parseFloat(charges.fixed).toFixed(2) + " " + sender_currency +" + " + parseFloat(percent).toFixed(2) + "%" + " = "+ parseFloat(charges.total).toFixed(2) + " " + sender_currency);
        }
        getFees();

        function getExchangeRate() {
            var sender_currency = acceptVar().sCurrency;
            var sender_currency_rate = acceptVar().sCurrency_rate;
            // console.log("sender_currency_rate",sender_currency_rate);
            var receiver_currency = acceptVar().rCurrency;
            var receiver_currency_rate = acceptVar().rCurrency_rate;
            // console.log("receiver_currency_rate",receiver_currency_rate);
            var rate = parseFloat(receiver_currency_rate) / parseFloat(sender_currency_rate);
            // console.log(rate);
            $('.rate-show').html("1 " + sender_currency + " = " + parseFloat(rate).toFixed(4) + " " + receiver_currency);

            return rate;
        }
        getExchangeRate();

        function getPreview() {

            var senderAmount = $("input[name=sender_amount]").val();
            var sender_currency = acceptVar().sCurrency;
            senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;

            // Sending Amount
            $('.request-amount').text(senderAmount + " " + sender_currency);

            var receiver_currency = acceptVar().rCurrency;
            var receiverAmount = receiveAmount();
            receiveAmount = parseFloat(receiverAmount).toFixed(2);
            $('.receive-amount').text(receiveAmount + " " + receiver_currency);

            $(".sender-currency").text(sender_currency + " (" + acceptVar().sCountry + ")");
            $(".receiver-currency").text(receiver_currency + " (" + acceptVar().rCountry + ")");

            // Fees
            var charges = feesCalculation();
            // console.log(total_charge + "--");
            $('.fees').text(charges.total + " " + sender_currency);

            // Pay In Total
            var pay_in_total = parseFloat(charges.total) + parseFloat(senderAmount);
            $('.pay-in-total').text(parseFloat(pay_in_total).toFixed(2) + " " + sender_currency);

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
                balance = parseFloat(balance).toFixed(2);
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
        run(JSON.parse(adSelectActiveItem("input[name=sender_currency]")),JSON.parse(adSelectActiveItem("input[name=receiver_currency]")));
    });

    $("input[name=sender_amount]").keyup(function(){
        run(JSON.parse(adSelectActiveItem("input[name=sender_currency]")),JSON.parse(adSelectActiveItem("input[name=receiver_currency]")));
    });

    var timeOut;
    $("input[name=receiver]").bind("keyup",function(){
        clearTimeout(timeOut);
        timeOut = setTimeout(getUser, 500,$(this).val(),"{{ setRoute('user.info') }}",$(this).parents(".input-group"));
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
    function getUser(string,URL,errorPlace = null) {
        if(string.length < 3) {
            return false;
        }

        var CSRF = laravelCsrf();
        var data = {
            _token      : CSRF,
            text        : string,
        };

        $.post(URL,data,function() {
            // success
        }).done(function(response){
            if(response.data == null) {
                if(errorPlace != null) {
                    $(errorPlace).css('border','1px solid rgba(153, 153, 153, 0.2)');
                    $(errorPlace).parent().find(".get-user-success").remove();
                    if($(errorPlace).parent().find(".get-user-error").length > 0) {
                        $(errorPlace).parent().find(".get-user-error").text("User doesn't exists");
                    }else {
                        $(`<span class="text--danger get-user-error mt-2" style="font-size:14px">User doesn't exists!</span>`).insertAfter($(errorPlace));
                    }
                }

            }else {
                if(errorPlace != null) {
                    var account_name = response.data.firstname +' '+response.data.lastname;
                    $(errorPlace).parent().find(".get-user-error").remove();
                    $(errorPlace).css('border','1px solid green');
                    if($(errorPlace).parent().find(".get-user-success").length > 0) {
                        $(errorPlace).parent().find(".get-user-success").text(account_name);
                    }else {
                        $(` <span class="text--success get-user-success mt-2" style="font-size:14px"><span class="text-white">Account Holder Name: </span> ${account_name}</span>`).insertAfter($(errorPlace));
                    }
                }
            }
        }).fail(function(response) {
            var response = JSON.parse(response.responseText);
            throwMessage(response.type,response.message.error);
        });
    }
</script>

@endpush
