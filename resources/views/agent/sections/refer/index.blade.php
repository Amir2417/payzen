@extends('agent.layouts.master')

@push('css')
    
@endpush

@section('content')
<div class="body-wrapper">
    <div class="row mb-30-none">
        <div class="col-xxl-5 col-xl-4 col-md-12 mb-30">
            <div class="p-4 card-user h-100">
                <div class="account-avatar-wrapper">
                    <div class="account-avatar">
                        <img class=" d-block mx-auto avater" src="{{ auth()->user()->agentImage }}" alt="" height="200" width="200">
                    </div>
                </div>
                <div>
                    <div
                        class="d-flex justify-content-between mt-4 rounded-2 p-2 user-card">
                        <p class=" m-0 fw-bold">{{ __("Total Refers") }}:</p>
                        <p class=" m-0 text--base">{{ $refer_users->count() }}</p>
                    </div>
                    <div
                        class="d-flex justify-content-between mt-4 rounded-2 p-2 user-card">
                        <p class=" m-0 fw-bold">{{ __("Refer code") }}:</p>
                        <div class="refer-code-area">
                            <p class=" m-0 copiable">{{ $auth_user->referral_id }}</p> 
                            <button class="copy-button"><i class="las la-copy"></i></button>
                        </div>
                    </div>
                </div>
                <div class="refer-link-wrapper">
                    <h4 class="title">{{ __("Refer Link") }}:</h4>
                    <span class="refer-link">{{ setRoute('user.register',$auth_user->referral_id) }}</span>
                    <ul class="refer-btn-list">
                        <li>
                            <button class="refer-btn copy-button">
                                <i class="las la-link"></i>
                            </button>
                            <span class="d-none copiable">{{ setRoute('user.register',$auth_user->referral_id) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="level-progress-area">
                    
                </div>
            </div>
        </div>
    </div>
    <!-- table -->
    <div class="table-area pt-40 pb-30">
        <div class="d-flex justify-content-between align-items-center my-escrow">
            <div class="dash-section-title">
                <h4>{{ __("Referral Agents") }}</h4>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-xl-12">
                <div class="table-area">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>{{ __("User Name") }}</th>
                                <th>{{ __("Refer Code") }}</th>
                                <th>{{ __("Joined Date") }}</th>
                                <th>{{ __("Referred Agents") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($refer_users as $item)
                                <tr>
                                    <td data-label="User Name">{{ $item->agent->fullname }}</td>
                                    <td data-label="Refer Code">{{ $item->agent->referral_id }}</td>
                                    <td data-label="Joined Date">{{ $item->agent->created_at->format('d-m-Y') }}</td>
                                    <td data-label="Referred Users">{{ $item->agent->referAgents->count() }}</td>
                                </tr>
                            @empty
                                @include('admin.components.alerts.empty',['colspan' => 4])
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ get_paginate($refer_users) }}

            </div>
        </div>
    </div>
</div>
@endsection

@push('script')

@endpush