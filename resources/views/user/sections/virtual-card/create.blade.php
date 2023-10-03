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
            <h3 class="title">{{ __("Add Card") }}</h3>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area d-flex align-items-center justify-content-between">
                        <div class="wrapper d-flex align-items-center">
                            <span class="dash-payment-badge">!</span>
                            <h5 class="title">{{ __("Add Card") }}</h5>
                        </div>
                    </div>
                    <div class="dash-payment-body">
                        <div class="card-wrapper mb-40"></div>
                        <form role="form" id="payment-form" action="{{ setRoute('user.virtual.card.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf                               
                            <div class="row mb-20-none">
                                <div class="col-md-6 form-group">
                                    <label for="name" class="form--label">{{ __("Name on Card") }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control custom-input" name="name" placeholder="Enter Name..."
                                            autocomplete="off" autofocus required />
                                        <span class="input-group-text bg--base"><i class="fa fa-font"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="cardNumber" class="form--label">{{ __("Card Number") }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control custom-input" name="card_number" placeholder="Enter Number..."
                                            autocomplete="off" required autofocus required />
                                        <span class="input-group-text bg--base"><i class="fa fa-credit-card"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="cardExpiry" class="form--label">{{ __("Expiration Date") }}</label>
                                    <input type="tel" class="form--control input-sz custom-input" name="expiration_date" placeholder="Enter Date..."
                                        autocomplete="off" required />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="cardCVC" class="form--label">{{ __("CVC Code") }}</label>
                                    <input type="tel" class="form--control input-sz custom-input" name="cvc_code" placeholder="Enter Code..."
                                        autocomplete="off" required />
                                </div>
                            </div>
                            <div class="btn-area text-center mt-30">
                                <button class="btn--base w-100" type="submit">{{ __("Save") }} <i class="fas fa-check-circle ms-1"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
<!-- card js -->
<script src="{{ asset('public/frontend/') }}/js/card.js"></script>

<script>
    (function($) {
        "use strict";
        var card = new Card({
            form: '#payment-form',
            container: '.card-wrapper',
            formSelectors: {
                numberInput: 'input[name="card_number"]',
                expiryInput: 'input[name="expiration_date"]',
                cvcInput: 'input[name="cvc_code"]',
                nameInput: 'input[name="name"]'
            }
        });
    })(jQuery);
</script>
@endpush
