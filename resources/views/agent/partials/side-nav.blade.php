
<div class="sidebar">
    <div class="sidebar-inner">
        <div class="sidebar-menu-inner-wrapper">
            <div class="sidebar-logo">
                <a href="{{ setRoute('index') }}" class="sidebar-main-logo">
                    <img src="{{ get_logo($basic_settings) }}" data-white_img="{{ get_logo($basic_settings,"dark") }}"
                    data-dark_img="{{ get_logo($basic_settings) }}" alt="logo">
                </a>
                <button class="sidebar-menu-bar">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </div>
            <div class="sidebar-menu-wrapper">
                <ul class="sidebar-menu">
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.dashboard') }}">
                            <i class="menu-icon fas fa-th-large"></i>
                            <span class="menu-title">{{ __("Dashboard") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.send.money.index') }}">
                            <i class="menu-icon fas fa-paper-plane"></i>
                            <span class="menu-title">{{ __("Send Money") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.receive.money.index') }}">
                            <i class="menu-icon fas fa-receipt"></i>
                            <span class="menu-title">{{__("Receive Money")}}</span>

                        </a>
                    </li>
                   
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a href="javascript:void(0)">
                            <i class="menu-icon fas fa-coins"></i>
                            <span class="menu-title">{{ __("Remittance") }}</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li class="sidebar-menu-item">
                                <a href="{{ setRoute('agent.remittance.index') }}" class="nav-link">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Send Remittance") }}</span>
                                </a>
                                <a href="{{ setRoute('agent.receipient.index') }}" class="nav-link">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Saved Recipient") }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.add.money.index') }}">
                            <i class="menu-icon fas fa-plus-circle"></i>
                            <span class="menu-title">{{ __("Add Money") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.withdraw.index') }}">
                            <i class="menu-icon fas fa-arrow-alt-circle-right"></i>
                            <span class="menu-title">{{ __("Withdraw Money") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.bill.pay.index') }}">
                            <i class="menu-icon fas fa-shopping-bag"></i>
                            <span class="menu-title">{{ __("Bill Pay") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.mobile.topup.index') }}">
                            <i class="menu-icon fas fa-mobile"></i>
                            <span class="menu-title">{{ __("Mobile ToUp") }}</span>
                        </a>
                    </li>
                    {{-- <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.stripe.card.index') }}">
                            <i class="menu-icon fas fa-mobile"></i>
                            <span class="menu-title">{{ __("Stripe Card") }}</span>
                        </a>
                    </li> --}}

                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.transactions.index') }}">
                            <i class="menu-icon fas fa-arrows-alt-h"></i>
                            <span class="menu-title">{{ __("Transactions") }}</span>
                        </a>
                    </li>
                    {{-- <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.sender.recipient.index') }}">
                            <i class="menu-icon fas fa-user-edit"></i>
                            <span class="menu-title">{{ __("Saved My Sender") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.receiver.recipient.index') }}">
                            <i class="menu-icon fas fa-user-check"></i>
                            <span class="menu-title">{{ __("Saved My Receiver") }}</span>
                        </a>
                    </li> --}}
                    <li class="sidebar-menu-item {{ Route::is('agent.refer.index') ? 'active' : '' }}">
                        <a href="{{ setRoute('agent.refer.index') }}">
                            <i class="menu-icon las la-user-circle"></i>
                            <span class="menu-title">{{ __("My Status") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('agent.security.google.2fa') }}">
                            <i class="menu-icon fas fa-qrcode"></i>
                            <span class="menu-title">{{ __("2FA Security") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="javascript:void(0)" class="logout-btn">
                            <i class="menu-icon fas fa-sign-out-alt"></i>
                            <span class="menu-title">{{ __("Logout") }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="sidebar-doc-box bg_img" data-background="{{ asset('public/frontend/') }}/images/element/support.jpg">
            <div class="sidebar-doc-icon">
                <i class="las la-question-circle"></i>
            </div>
            <div class="sidebar-doc-content">
                <h4 class="title">{{ __("Need Help?") }}</h4>
                <p>{{ __("Please check our docs") }}</p>
                <div class="sidebar-doc-btn">
                    <a href="{{ setRoute('agent.support.ticket.index') }}" class="btn--base w-100">{{ __("Get Support") }}</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
    <script>
        $(".logout-btn").click(function(){
            var actionRoute =  "{{ setRoute('agent.logout') }}";
            var target      = 1;
            var message     = `Are you sure to <strong>Logout</strong>?`;

            openAlertModal(actionRoute,target,message,"Logout","POST");
        });
    </script>
@endpush
