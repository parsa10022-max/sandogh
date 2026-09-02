@extends('customer.layouts.app')

@section('title', 'پروفایل')

@section('content')

    <div class="container-fluid px-0 customer-profile">

        {{-- Page Header --}}
        <div class="customer-profile-header">

            <h1 class="customer-profile-title">
                <i class="bi bi-person-circle"></i>
                پروفایل من
            </h1>

            <p class="customer-profile-description">
                اطلاعات حساب و عضویت شما
            </p>

        </div>

        {{-- Profile Card --}}
        <div class="card customer-profile-card">

            <div class="card-body">

                {{-- Profile Summary --}}
                <div class="customer-profile-summary">

                    <div class="customer-profile-avatar">
                        <i class="bi bi-person"></i>
                    </div>

                    <h2 class="customer-profile-name">
                        {{ $customer?->full_name ?? 'مشتری محترم' }}
                    </h2>

                    <span class="customer-profile-code">
                        کد عضویت:
                        {{ $customer?->customer_code ?? '—' }}
                    </span>

                </div>

                {{-- Profile Information --}}
                <div class="row g-2">

                    <div class="col-12 col-md-6">

                        <div class="customer-profile-field">

                            <label class="customer-profile-label">
                                نام
                            </label>

                            <div class="customer-profile-value">
                                {{ $customer?->first_name ?? '—' }}
                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="customer-profile-field">

                            <label class="customer-profile-label">
                                نام خانوادگی
                            </label>

                            <div class="customer-profile-value">
                                {{ $customer?->last_name ?? '—' }}
                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="customer-profile-field">

                            <label class="customer-profile-label">
                                نام پدر
                            </label>

                            <div class="customer-profile-value">
                                {{ $customer?->father_name ?? '—' }}
                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="customer-profile-field">

                            <label class="customer-profile-label">
                                شماره موبایل
                            </label>

                            <div class="customer-profile-value customer-profile-ltr">
                                {{ $customer?->mobile ?? $user->mobile ?? '—' }}
                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="customer-profile-field">

                            <label class="customer-profile-label">
                                کد ملی
                            </label>

                            <div class="customer-profile-value customer-profile-ltr">
                                {{ $customer?->national_code ?? '—' }}
                            </div>

                        </div>

                    </div>

                    <div class="col-12 col-md-6">

                        <div class="customer-profile-field">

                            <label class="customer-profile-label">
                                شماره شبا
                            </label>

                            <div class="customer-profile-value customer-profile-ltr customer-profile-iban">
                                @if($customer?->iban)
                                    {{ implode(' ', str_split(str_replace(' ', '', $customer->iban), 4)) }}
                                @else
                                    —
                                @endif
                            </div>

                        </div>

                    </div>



                </div>

            </div>

        </div>

    </div>

@endsection
