<div class="sidebar">
    <div class="sidebar-inner">
        <div class="sidebar-menu-inner-wrapper">
            <div class="sidebar-logo">
                <a href="{{ setRoute('index') }}" class="sidebar-main-logo theme-change">
                    <img src="{{ get_logo($basic_settings) }}" white-img="{{ get_logo($basic_settings) }}"
                    dark-img="{{ get_logo($basic_settings,'dark') }}" alt="logo">
                </a>
                <button class="sidebar-menu-bar">
                    <i class="fas fa-exchange-alt"></i>
                </button>
            </div>
            <div class="sidebar-menu-wrapper">
                <ul class="sidebar-menu">
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('user.dashboard') }}">
                            <i class="menu-icon fas fa-th-large"></i>
                            <span class="menu-title">{{ __("Dashboard") }}</span>
                        </a>
                    </li>
                    @if(module_access('send-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('user.send.money.index') }}">
                            <i class="menu-icon fas fa-paper-plane"></i>
                            <span class="menu-title">{{ __("Send Money") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(module_access('receive-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('user.receive.money.index') }}">
                            <i class="menu-icon fas fa-receipt"></i>
                            <span class="menu-title">{{ __("Receive Money") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(module_access('remittance-money',$module)->status)
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a href="javascript:void(0)">
                            <i class="menu-icon fas fa-coins"></i>
                            <span class="menu-title">{{ __("Remittance") }}</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li class="sidebar-menu-item">
                                <a href="{{ setRoute('user.remittance.index') }}" class="nav-link">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Send Remittance") }}</span>
                                </a>
                                <a href="{{ setRoute('user.receipient.index') }}" class="nav-link">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Saved Recipient") }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    @endif
                    @if(module_access('add-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('user.add.money.index') }}">
                            <i class="menu-icon fas fa-plus-circle"></i>
                            <span class="menu-title">{{ __("Add Money") }}</span>
                        </a>
                    </li>
                    @endif
                    @if(module_access('withdraw-money',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('user.money.out.index') }}">
                            <i class="menu-icon fas fa-arrow-alt-circle-right"></i>
                            <span class="menu-title">{{ __("Withdraw Money") }}</span>
                        </a>
                    </li>
                    @endif
                   
                    @if(module_access('make-payment',$module)->status)
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('user.make.payment.index') }}">
                            <i class="menu-icon fas fa-arrow-alt-circle-left"></i>
                            <span class="menu-title">{{ __("Make Payment") }}</span>
                        </a>
                    </li>
                    @endif
                    <li class="sidebar-menu-item sidebar-dropdown">
                        <a href="javascript:void(0)">
                            <i class="menu-icon fas fa-shopping-bag"></i>
                            <span class="menu-title">{{ __("Utility") }}</span>
                        </a>
                        <ul class="sidebar-submenu">
                            <li class="sidebar-menu-item">
                                @if(module_access('bill-pay',$module)->status)
                                <a href="{{ setRoute('user.bill.pay.index') }}" class="nav-link">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Bill Pay") }}</span>
                                </a>
                                @endif
                                @if(module_access('mobile-top-up',$module)->status)
                                <a href="{{ setRoute('user.mobile.topup.index') }}" class="nav-link">
                                    <i class="menu-icon las la-ellipsis-h"></i>
                                    <span class="menu-title">{{ __("Mobile ToUp") }}</span>
                                </a>
                                @endif
                            </li>
                        </ul>
                    </li>
                    @if(module_access('virtual-card',$module)->status)
                        @if(virtual_card_system('flutterwave'))
                        <li class="sidebar-menu-item">
                            <a href="{{ setRoute('user.virtual.card.index') }}">
                                <i class="menu-icon fas fa-link"></i>
                                <span class="menu-title">{{ __("Link Card") }}</span>
                            </a>
                        </li>
                        @elseif(virtual_card_system('sudo'))
                        <li class="sidebar-menu-item">
                            <a href="{{ setRoute('user.sudo.virtual.card.index') }}">
                                <i class="menu-icon fas fa-link"></i>
                                <span class="menu-title">{{ __("Link Card") }}</span>
                            </a>
                        </li>
                        @endif
                    @endif
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('user.transactions.index') }}">
                            <i class="menu-icon fas fa-arrows-alt-h"></i>
                            <span class="menu-title">{{ __("Transaction") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item">
                        <a href="{{ setRoute('user.security.google.2fa') }}">
                            <i class="menu-icon fas fa-qrcode"></i>
                            <span class="menu-title">{{ __("2FA Security") }}</span>
                        </a>
                    </li>
                    <li class="sidebar-menu-item {{ Route::is('user.refer.index') ? 'active' : '' }}">
                        <a href="{{ setRoute('user.refer.index') }}">
                            <i class="menu-icon las la-user-circle"></i>
                            <span class="menu-title">{{ __("My Status") }}</span>
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
        <div class="sidebar-doc-box">
            <div class="sidebar-doc-icon">
                <i class="las la-headset"></i>
            </div>
            <div class="sidebar-doc-content">
                <h4 class="title">{{ __("Help Center") }}</h4>
                <p>{{ __("How can we help you?") }}</p>
                <div class="sidebar-doc-btn">
                    <a href="{{ setRoute('user.support.ticket.index') }}" class="btn--base w-100">{{ __("Get Support") }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('script')
    <script>
        $(".logout-btn").click(function(){
            var actionRoute =  "{{ setRoute('user.logout') }}";
            var target      = 1;
            var message     = `Are you sure to <strong>Logout</strong>?`;

            openAlertModal(actionRoute,target,message,"Logout","POST");
        });
    </script>
@endpush
