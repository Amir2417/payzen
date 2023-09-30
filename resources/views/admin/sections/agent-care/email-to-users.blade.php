@extends('admin.layouts.master')

@push('css')
@endpush

@section('page-title')
    @include('admin.components.page-title', ['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb', [
        'breadcrumbs' => [
            [
                'name' => __('Dashboard'),
                'url' => setRoute('admin.dashboard'),
            ],
        ],
        'active' => __('Agent Care'),
    ])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("Email To Agents") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" action="{{ setRoute('admin.agents.email.agents.send') }}" method="post">
                @csrf
                <div class="row mb-10-none">
                    <div class="col-xl-6 col-lg-6 form-group">
                        <label>{{ __("Agent*") }}</label>
                        <select class="form--control nice-select" name="user_type">
                            <option selected disabled>Select Users</option>
                            <option value="all">All Agents</option>
                            <option value="active">Active Agents</option>
                            <option value="email_verified">Email Unverified</option>
                            <option value="kyc_verified">Kyc Unverified</option>
                            <option value="banned">Banned Agents</option>
                        </select>
                    </div>
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input',[
                            'label'         => 'Subject*',
                            'name'          => 'subject',
                            'value'         => old('subject'),
                            'placeholder'   => "Write Here...",
                        ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.input-text-rich',[
                            'label'         => 'Details*',
                            'name'          => 'message',
                            'value'         => old('message'),
                            'placeholder'   => "Write Here...",
                        ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'permission'    => "admin.agents.email.users.send",
                            'text'          => "Send Email",
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
@endpush
