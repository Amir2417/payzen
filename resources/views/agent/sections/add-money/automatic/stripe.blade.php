@extends('agent.layouts.master')

@push('css')
<style>
    .jp-card .jp-card-back, .jp-card .jp-card-front {

      background-image: linear-gradient(160deg, #084c7c 0%, #55505e 100%) !important;
      }
      label{
          color: #000 !important;
      }
      .form--control{
          color: #000 !important;
      }
      .cancel-button {
            position: relative;
            background: #ff0000;
            color: #fff;
            border-radius: 10px;
            padding: 15px 25px;
            font-family: "Exo 2", sans-serif;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            -webkit-transition: all ease 0.5s;
            transition: all ease 0.5s;
        }
  </style>
@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
        ]
    ], 'active' => __("Stripe Payment")])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{ __("Saved Cards") }}</h3>
            <div class="d-flex justify-content-center">
                <a href="{{ setRoute('agent.stripe.card.add',$gateway) }}" class="btn--base m-2">{{ ("Add Card") }} <i class="las la-plus"></i></a>
                <a href="{{ setRoute('agent.add.money.payment.cancel',@$hasData->type) }}" class="cancel-button m-2">{{ ("Cancel") }}</a>
            </div>
        </div>
    </div>
    <form action="{{ setRoute('agent.add.money.stripe.payment.confirmed') }}" method="post">
        <input type="hidden" class="hidden-value" name="id">
        @csrf
        <div class="row mb-30-none">
            @php
                $step = 0;
            @endphp
            
            @forelse ($stripe_cards as $item)
                @php
                    $step++;
                @endphp 
                <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-6 mb-30">
                    <div class="link-card-item">
                        <div class="title-area">
                            <div class="h5 title">Card {{ $step }}</div>
                            <button type="button" class="btn btn--base select-btn" data-item='{{ json_encode($item) }}'>Select</button>
                        </div>
                        <div class="link-card-wrapper">
                            <div class="link-card bg_img" data-background="{{ asset('public/frontend/images/account/account.jpg') }}">
                                <div class="top">
                                    <h2>{{ decrypt(@$item->name) }}</h2>
                                    <img src="{{ asset("public/frontend/images/element/stripe.png") }}" />
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
                                                <img src="{{ asset("public/frontend/images/element/visa.png") }}" class="brand" />
                                            </section>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>    
            @empty
                <div class="alert alert-primary text-center">
                    {{ __("No Card Found! Create a new card.") }}
                </div>
            @endforelse
        </div>
        @if (count($stripe_cards) > 0)
            <div class="money-tranasfer-btn text-center">
                <button type="submit" class="btn--base w-100">Next</button>
            </div>
        @endif
    </form>
</div>
@endsection

@push('script')

    <script>
        (function ($) {
            "use strict";
            var card = new Card({
                form: '#payment-form',
                container: '.card-wrapper',
                formSelectors: {
                    numberInput: 'input[name="cardNumber"]',
                    expiryInput: 'input[name="cardExpiry"]',
                    cvcInput: 'input[name="cardCVC"]',
                    nameInput: 'input[name="name"]'
                }
            });
        })(jQuery);
    </script>
    <script>
        $('.cancel-btn').click(function(){
            var dataHref = $(this).data('href');
            if(confirm("Are you sure?") == true) {
                window.location.href = dataHref;
            }
        });
      </script>
    <script>
        $('.select-btn').on('click',function(){
            var selectData = JSON.parse($(this).attr('data-item'));
            var hiddenValue = $('.hidden-value').val(selectData.id);
            $(this).text('Selected');
        });
    </script>
@endpush
