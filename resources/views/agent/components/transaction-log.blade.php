@isset($transactions)
    @forelse ($transactions as $item)
        <div class="dashboard-list-item-wrapper">
            <div class="dashboard-list-item sent">
                <div class="dashboard-list-left">
                    <div class="dashboard-list-user-wrapper">
                        <div class="dashboard-list-user-icon">
                            @if ($item->attribute == payment_gateway_const()::SEND)
                            <i class="las la-arrow-up"></i>
                            @else
                            <i class="las la-arrow-down"></i>
                            @endif
                        </div>
                        <div class="dashboard-list-user-content">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <h4 class="title">{{ __("Add Balance via") }} <span class="text--warning">{{ $item->currency->name }}</span></h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <h4 class="title">{{ __("Money Out") }} <span class="text--warning">{{ $item->currency->gateway->name }}</span></h4>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <h4 class="title">{{ __("Bill Pay") }} <span class="text--warning">({{ @$item->details->bill_type_name }})</span></h4>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <h4 class="title">{{ __("Mobile Topup") }} <span class="text--warning">({{ @$item->details->topup_type_name }})</span></h4>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                <h4 class="title">{{ __("Virtual Card") }} <span class="text--info">({{ @$item->remark }})</span></h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <h4 class="title">{{ __("Exchange Money") }} <span class="text--warning">{{ $item->details->request_currency }} To {{ $item->details->exchange_currency }}</span></h4>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <h4 class="title">{{ __("Balance Update From Admin (".$item->creator_wallet->currency->code.")") }} </h4>
                            @elseif ($item->type == payment_gateway_const()::TYPETRANSFERMONEY)
                                @if ($item->isAuthUserAgent())

                                @if ($item->attribute == payment_gateway_const()::SEND)
                                    <h4 class="title">{{ __("Send Money to @" . @$item->details->receiver_username." (".@$item->details->receiver_email.")") }} </h4>
                                @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                                    <h4 class="title">{{ __("Received Money from @" .@$item->details->sender_username." (".@$item->details->sender_email.")") }} </h4>
                                @endif
                                @endif
                            @elseif ($item->type == payment_gateway_const()::TYPEMAKEPAYMENT)
                                @if ($item->isAuthUserAgent())

                                    @if ($item->attribute == payment_gateway_const()::SEND)
                                        <h4 class="title">{{ __("Make Payment to @" . @$item->details->receiver->fullname." (".@$item->details->receiver->full_mobile.")") }} </h4>
                                    @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                                        <h4 class="title">{{ __("Make Payment from @" .@$item->details->sender->fullname." (".@$item->details->sender->full_mobile.")") }} </h4>
                                    @endif
                                @endif
                            @elseif ($item->type == payment_gateway_const()::AGENTMONEYOUT)
                                @if ($item->isAuthUserAgent())

                                    @if ($item->attribute == payment_gateway_const()::SEND)
                                        <h4 class="title">{{ __("Money Out to @" . @$item->details->receiver->fullname." (".@$item->details->receiver->full_mobile.")") }} </h4>
                                    @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                                        <h4 class="title">{{ __("Money Out From @" .@$item->details->sender->fullname." (".@$item->details->sender->full_mobile.")") }} </h4>
                                    @endif
                                @endif

                            @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                                @if ($item->isAuthUserAgent())
                                    @if ($item->attribute == payment_gateway_const()::SEND)
                                        <h4 class="title">{{ __("Send Remitance to @" . $item->details->receiver->firstname.' '.@$item->details->receiver->lastname." (".@$item->details->receiver->mobile_code.@$item->details->receiver->mobile.")") }} </h4>
                                    @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                                        <h4 class="title">{{ __("Received Remitance from @" .@$item->details->sender->fullname." (".@$item->details->sender->full_mobile.")") }} </h4>
                                    @endif
                                @endif
                            @endif
                            <span class="{{ $item->stringStatus->class }}">{{ $item->stringStatus->value }} </span>
                        </div>
                    </div>
                </div>
                <div class="dashboard-list-right">
                    @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                        <h4 class="main-money text--warning">{{ get_amount($item->request_amount,$item->info->sender_currency->code) }}</h4>
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->payable,$item->currency->currency_code) }}</h6>
                    @elseif($item->type == payment_gateway_const()::TYPEMONEYOUT)
                        <h6 class="exchange-money text--warning fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        <h4 class="main-money ">{{ get_amount($item->payable,$item->currency->currency_code) }}</h4>
                    @elseif($item->type == payment_gateway_const()::BILLPAY)
                        <h4 class="main-money text--warning">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h4>
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h6>
                    @elseif($item->type == payment_gateway_const()::MOBILETOPUP)
                        <h4 class="main-money text--warning">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h4>
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h6>
                    @elseif($item->type == payment_gateway_const()::VIRTUALCARD)
                        <h4 class="main-money text--warning">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h4>
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                        <h4 class="main-money text--base">{{ get_amount($item->request_amount,$item->creator_wallet->currency->code) }}</h4>
                        <h6 class="exchange-money">{{ get_amount($item->available_balance,$item->creator_wallet->currency->code) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                        <h4 class="main-money text--base">{{ get_amount($item->request_amount,$item->creator_wallet->currency->code) }}</h4>
                        <h6 class="exchange-money">{{ get_amount($item->available_balance,$item->creator_wallet->currency->code) }}</h6>
                    @elseif ($item->type == payment_gateway_const()::TYPETRANSFERMONEY)
                        @if ($item->attribute == payment_gateway_const()::SEND)
                        <h6 class="exchange-money text--warning ">{{ get_amount($item->request_amount,$item->details->charges->sender_currency) }}</h6>
                        <h4 class="main-money fw-bold">{{ get_amount($item->payable,$item->details->charges->sender_currency) }}</h4>
                        @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->request_amount,$item->details->charges->receiver_currency) }}</h6>
                        @endif
                    @elseif ($item->type == payment_gateway_const()::TYPEMAKEPAYMENT)
                        @if ($item->attribute == payment_gateway_const()::SEND)
                        <h6 class="exchange-money text--warning ">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        <h4 class="main-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h4>
                        @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        @endif
                    @elseif ($item->type == payment_gateway_const()::AGENTMONEYOUT)
                        @if ($item->attribute == payment_gateway_const()::SEND)
                        <h6 class="exchange-money text--warning ">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        <h4 class="main-money fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</h4>
                        @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        @endif
                    @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                        @if ($item->attribute == payment_gateway_const()::SEND)
                        <h6 class="exchange-money text--warning ">{{ get_amount($item->request_amount,@$item->details->sender_currency->code) }}</h6>
                        <h4 class="main-money fw-bold">{{ get_amount($item->payable,@$item->details->sender_currency->code) }}</h4>
                        @elseif ($item->attribute == payment_gateway_const()::RECEIVED)
                        <h6 class="exchange-money fw-bold">{{ get_amount($item->request_amount,get_default_currency_code()) }}</h6>
                        @endif
                    @endif
                </div>
            </div>
            <div class="preview-list-wrapper">

                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="lab la-tumblr"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Transaction ID") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->trx_id }}</span>
                    </div>
                </div>
                @if ($item->type != payment_gateway_const()::TYPETRANSFERMONEY )
                @if ($item->type != payment_gateway_const()::TYPEMAKEPAYMENT )
                @if ($item->type != payment_gateway_const()::BILLPAY )
                @if ($item->type != payment_gateway_const()::MOBILETOPUP )
                @if ($item->type != payment_gateway_const()::VIRTUALCARD )
                @if ($item->type != payment_gateway_const()::SENDREMITTANCE )
                @if ($item->type != payment_gateway_const()::AGENTMONEYOUT )

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
                        @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                            <span>{{ $item->info->sender_currency->rate }}  {{ $item->info->sender_currency->code }} = {{ get_amount($item->info->payment_currency->rate,$item->info->payment_currency->code) }}</span>
                        @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->details->to_country->rate,$item->details->to_country->code) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->currency->rate,$item->currency->currency_code) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                            <span>1 {{ $item->creator_wallet->currency->code }} = {{ get_amount($item->details->exchange_rate,$item->details->exchange_currency) }}</span>
                        @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                            <span>1 {{ get_default_currency_code() }} = {{ get_amount($item->creator_wallet->currency->rate,$item->creator_wallet->currency->code) }}</span>

                        @endif
                    </div>
                </div>
                @endif
                @endif
                @endif
                @endif
                @endif
                @endif
                @endif

                @if ($item->type == payment_gateway_const()::BILLPAY )
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-balance-scale"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Bill Type") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->bill_type_name }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-balance-scale"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Customer Identifier") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->customer_identifier }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-receipt"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Flutterwave Reference") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->flw_info->flw_ref }}</span>
                    </div>
                </div>
                @endif
                @if ($item->type == payment_gateway_const()::MOBILETOPUP )
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-balance-scale"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Topup Type") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->topup_type_name }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="fas fa-mobile"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Mobile Number") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text--base">{{ @$item->details->mobile_number }}</span>
                    </div>
                </div>
                @endif

                @if ($item->type == payment_gateway_const()::TYPETRANSFERMONEY )
                    @if ($item->attribute == payment_gateway_const()::SEND)
                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="las la-battery-half"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Fees & Charge") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">

                                <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code) }}</span>
                            </div>
                        </div>
                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="lab la-get-pocket"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Recipient Received") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span>{{ get_amount($item->details->charges->sender_amount,$item->details->charges->receiver_currency) }}</span>
                            </div>
                        </div>

                        <div class="preview-list-item">
                            <div class="preview-list-left">
                                <div class="preview-list-user-wrapper">
                                    <div class="preview-list-user-icon">
                                        <i class="las la-balance-scale"></i>
                                    </div>
                                    <div class="preview-list-user-content">
                                        <span>{{ __("Current Balance") }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="preview-list-right">
                                <span class="text--base">{{ get_amount($item->available_balance,$item->details->charges->sender_currency) }}</span>
                            </div>
                        </div>
                    @else
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-balance-scale"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Current Balance") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            <span class="text--base">{{ get_amount($item->available_balance,$item->user_wallet->currency->code) }}</span>
                        </div>
                    </div>
                    @endif
                @else
                    @if ($item->type != payment_gateway_const()::SENDREMITTANCE )
                    @if ($item->type != payment_gateway_const()::AGENTMONEYOUT )
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-battery-half"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Fees & Charge") }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span>{{ get_amount($item->charge->total_charge,$item->currency->currency_code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <span>{{ get_amount($item->charge->total_charge,$item->currency->currency_code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::SENDREMITTANCE)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                <span>{{ get_amount($item->charge->total_charge,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span>{{ get_amount($item->details->total_charge,$item->creator_wallet->currency->code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span>{{ get_amount($item->charge->total_charge,$item->creator_wallet->currency->code) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @if ($item->type != payment_gateway_const()::BILLPAY)
                    @if ($item->type != payment_gateway_const()::MOBILETOPUP)
                    @if ($item->type != payment_gateway_const()::SENDREMITTANCE)

                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="lab la-get-pocket"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::AGENTMONEYOUT)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                        <span>{{ __("Conversion Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                        <span>{{ __("Payable Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                        <span>{{ __("Payable Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                        <span>{{ __("Total Payable") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                        <span>{{ __("Total Received") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                        <span>{{ __("Card Amount") }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span class="text-success">{{ get_amount($item->available_balance,$item->info->sender_currency->code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::AGENTMONEYOUT)
                                <span class="text-success">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                             @php
                                 $conversionAmount = $item->request_amount * $item->currency->rate;
                             @endphp
                                <span>{{ get_amount($conversionAmount,$item->currency->currency_code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span class="fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span class="fw-bold">{{ get_amount($item->payable,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                <span class="fw-bold"> {{ get_amount(@$item->details->card_info->amount,get_default_currency_code()) }}</span>

                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span>{{ get_amount($item->payable,$item->creator_wallet->currency->code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span>{{ get_amount($item->payable,$item->creator_wallet->currency->code) }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endif

                    @if ($item->type != payment_gateway_const()::TYPEADDMONEY)
                    @if ($item->type != payment_gateway_const()::SENDREMITTANCE)
                    @if ($item->type != payment_gateway_const()::AGENTMONEYOUT)
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-receipt"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                        <span>{{ __("Total Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                        <span>{{ __("Current Balance") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                        <span>{{ __("Card Number") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                        <span>{{ __("Exchange Amount") }}</span>
                                    @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                        <span>{{ __("Remark") }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="preview-list-right">
                            @if ($item->type == payment_gateway_const()::TYPEADDMONEY)
                                <span class="text--warning">{{ get_amount($item->payable,$item->currency->currency_code) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYOUT)
                                <span class="text--danger">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::BILLPAY)
                                <span class="text--danger">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::MOBILETOPUP)
                                <span class="text--danger">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                            @elseif ($item->type == payment_gateway_const()::VIRTUALCARD)
                                @php
                                    $card_pan = str_split(@$item->details->card_info->card_pan, 4);
                                @endphp
                                @foreach($card_pan as $key => $value)
                                <span class="text--base fw-bold">{{ $value }}</span>
                                @endforeach
                            @elseif ($item->type == payment_gateway_const()::TYPEMONEYEXCHANGE)
                                <span class="text--warning">{{ get_amount($item->details->exchange_amount,$item->details->exchange_currency) }}</span>
                            @elseif ($item->type == payment_gateway_const()::TYPEADDSUBTRACTBALANCE)
                                <span class="text--warning">{{ $item->remark }}</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endif
                    @endif
                @endif
                @if ($item->type == payment_gateway_const()::VIRTUALCARD)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-smoking"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Current Balance") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="fw-bold">{{ get_amount($item->available_balance,get_default_currency_code()) }}</span>
                    </div>
                </div>
                @endif
                @if ($item->type == payment_gateway_const()::SENDREMITTANCE)
                @if ($item->attribute == payment_gateway_const()::SEND)
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
                        <span>{{ @$item->details->sender_currency->rate }} {{ @$item->details->sender_currency->code }} = {{ get_amount($item->details->to_country->rate,$item->details->to_country->code) }}</span>
                    </div>
                </div>

                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-battery-half"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Fees & Charge") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ get_amount($item->charge->total_charge,@$item->details->sender_currency->code) }}</span>
                    </div>
                </div>
                @endif
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-flag"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Sending Country") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ @$item->details->sender_currency->country ?? '' }}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-flag"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Receiving Country") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ @$item->details->to_country->country }}</span>
                    </div>
                </div>
                @if ($item->attribute == payment_gateway_const()::SEND)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-user-tag"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Sender Recipient Name") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ @$item->details->sender->firstname.' '.@$item->details->sender->lastname}}</span>
                    </div>
                </div>
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-user-tag"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Receiver Recipient Name") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ @$item->details->receiver->firstname.' '.@$item->details->receiver->lastname}}</span>
                    </div>
                </div>
                @endif
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-cash-register"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Transaction Type") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                            @if( @$item->details->remitance_type == "wallet-to-wallet-transfer")
                                    <span class="text-base"> {{@$basic_settings->site_name}} {{__("Wallet")}}</span>
                                    @else
                                    <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$item->details->remitance_type))}}</span>

                            @endif
                    </div>
                </div>
                @if( @$item->details->remitance_type == "bank-transfer")
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-piggy-bank"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Bank Name") }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="preview-list-right">
                        <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$item->details->receiver->alias))}}</span>
                        </div>
                    </div>
                @endif
                @if( @$item->details->remitance_type == "cash-pickup")
                    <div class="preview-list-item">
                        <div class="preview-list-left">
                            <div class="preview-list-user-wrapper">
                                <div class="preview-list-user-icon">
                                    <i class="las la-piggy-bank"></i>
                                </div>
                                <div class="preview-list-user-content">
                                    <span>{{ __("Pickup Point") }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="preview-list-right">
                        <span class="text-base"> {{ ucwords(str_replace('-', ' ', @$item->details->receiver->alias))}}</span>
                        </div>
                    </div>
                @endif
                 @if ($item->attribute == payment_gateway_const()::SEND)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-piggy-bank"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Receipient Get") }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-list-right">
                    <span class="text-base fw-bold"> {{ number_format(@$item->details->recipient_amount,2)}} {{ $item->details->to_country->code }}</span>
                    </div>
                </div>
                @endif
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-smoking"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Current Balance") }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="preview-list-right">
                    <span class="text-base fw-bold"> {{ get_amount($item->available_balance,$item->details->sender_currency->code) }}</span>
                    </div>
                </div>
                @endif
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-clock"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Time & Date") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span>{{ $item->created_at->format('d-m-y h:i:s A') }}</span>
                    </div>
                </div>

                @if( $item->status == 4 &&  $item->reject_reason != null)
                <div class="preview-list-item">
                    <div class="preview-list-left">
                        <div class="preview-list-user-wrapper">
                            <div class="preview-list-user-icon">
                                <i class="las la-smoking"></i>
                            </div>
                            <div class="preview-list-user-content">
                                <span>{{ __("Rejection Reason") }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="preview-list-right">
                        <span class="text-danger">{{ $item->reject_reason }}</span>
                    </div>
                </div>
                @endif



            </div>
        </div>
    @empty
        <div class="alert alert-primary text-center">
            {{ __("No Record Found!") }}
        </div>
    @endforelse

    {{ get_paginate($transactions) }}


@endisset
