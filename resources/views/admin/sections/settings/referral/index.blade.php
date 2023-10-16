@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("Referral Settings")])
@endsection

@section('content')

    <div class="custom-card">

        <div class="card-header">
            <h6 class="title">{{ __("New Registration Bonus") }}</h6>
        </div>

        <div class="card-body">

            <div class="card-title mb-2">
                <p class="f-sm fw-bold text--info">{{ __("Please click update button to make any changes") }}</p>
            </div>

            <form class="card-form" method="POST" action="{{ setRoute('admin.settings.referral.update') }}">
                @csrf
                <div class="row">
                    <div class="col-3 mb-4 form-group">
                        <label>{{ __("Bonus") }} ({{ __("Amount") }})<span>*</span></label>
                        <div class="input-group">
                            <input type="text" class="form--control number-input" name="bonus" value="{{ old('bonus',$referral_settings->bonus ?? "") }}" placeholder="Enter New User Bonus">
                            <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                        </div>

                    </div>
                    <div class="col-3 mb-4">
                        @include('admin.components.form.switcher',[
                            'label'         => 'Balance added to',
                            'name'          => 'wallet_type',
                            'value'         => old('wallet_type', $referral_settings->wallet_type ?? "c_balance"),
                            'options'       => ['Wallet Balance' => 'c_balance','Profit Balance' => 'p_balance'],
                        ])
                    </div>
                    <div class="col-3 mb-4">
                        @include('admin.components.form.switcher',[
                            'label'         => 'Mail Notification',
                            'name'          => 'mail',
                            'value'         => old('mail', $referral_settings->mail ?? 1),
                            'options'       => ['Enable' => 1,'Disable' => 0],
                        ])
                    </div>
                    <div class="col-3 mb-4">
                        @include('admin.components.form.switcher',[
                            'label'         => 'Status',
                            'name'          => 'status',
                            'value'         => old('status', $referral_settings->status ?? 0),
                            'options'       => ['Enable' => 1,'Disable' => 0],
                        ])
                    </div>
                </div>

                <div class="col-xl-12 col-lg-12">
                    @include('admin.components.button.form-btn',[
                        'class'         => "w-100 btn-loading",
                        'text'          => "Update",
                    ])
                </div>
            </form>
        </div>

    </div>


@endsection

@push('script')


@endpush
