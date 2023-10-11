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
            <h3 class="title">{{ __("Stripe Card") }}</h3>
            <a href="{{ setRoute('agent.stripe.card.create') }}" class="btn--base">{{ ("Add Card") }} <i class="las la-plus"></i></a>
        </div>
    </div>
    <div class="row mb-30-none">
        @php
            $step = 0;
        @endphp
        @if(isset($stripe_cards))
            @foreach ($stripe_cards ?? [] as $item)
            @php
                $step++;
            @endphp
            <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 mb-30">
                <div class="link-card-item">
                    <div class="title-area">
                        <div class="h5 title">Card {{ $step }}</div>
                        <button class="link-card-remove-btn"><i class="fas fa-trash"></i> Remove</button>
                    </div>
                    <div class="link-card-wrapper">
                        <div class="link-card bg_img" data-background="{{ asset('public/frontend/images/account/account.jpg') }}">
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

@endpush
