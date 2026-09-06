@extends('layouts.app')

@section('title', 'اطلاعات حساب')

@section('content')

    <div class="container-fluid account-show-page">

        {{-- سربرگ صفحه --}}
        <div class="account-show-header">

            <div class="account-show-title-wrapper">

                <div class="account-show-title-icon">
                    <i class="bi bi-bank"></i>
                </div>

                <div>
                    <h5 class="account-show-title">
                        اطلاعات حساب
                    </h5>

                    <p class="account-show-subtitle">
                        مشاهده اطلاعات و عملیات حساب
                    </p>
                </div>

            </div>

            <a
                href="{{ route('accounts.index') }}"
                class="btn account-back-btn"
            >
                <i class="bi bi-arrow-right"></i>
                <span>بازگشت به حساب‌ها</span>
            </a>

        </div>


        {{-- اطلاعات حساب --}}
        <div class="card account-info-card">

            <div class="card-header account-info-header">

                <div class="account-section-title">

                <span class="account-section-icon">
                    <i class="bi bi-info-circle"></i>
                </span>

                    <span>
                    مشخصات حساب
                </span>

                </div>

            </div>


            <div class="card-body account-info-body">

                <div class="account-info-grid">

                    {{-- صاحب حساب --}}
                    <div class="account-info-item">

                    <span class="account-info-label">
                        <i class="bi bi-person"></i>
                        صاحب حساب
                    </span>

                        <div class="account-info-value">

                            @if($account->customer)

                                <span class="account-type-badge customer">
                                <i class="bi bi-person-fill"></i>
                                مشتری
                            </span>

                                <strong>
                                    {{ $account->customer->first_name }}
                                    {{ $account->customer->last_name }}
                                </strong>

                            @else

                                <span class="account-type-badge system">
                                <i class="bi bi-bank2"></i>
                                سیستمی
                            </span>

                                <strong>
                                    {{ $account->name ?? '-' }}
                                </strong>

                            @endif

                        </div>

                    </div>


                    {{-- کد مشتری --}}
                    @if($account->customer)

                        <div class="account-info-item">

                        <span class="account-info-label">
                            <i class="bi bi-person-vcard"></i>
                            کد مشتری
                        </span>

                            <strong class="account-info-value account-code">
                                {{ $account->customer->customer_code }}
                            </strong>

                        </div>

                    @endif


                    {{-- شماره حساب --}}
                    <div class="account-info-item">

                    <span class="account-info-label">
                        <i class="bi bi-credit-card"></i>
                        شماره حساب
                    </span>

                        <strong
                            class="account-info-value account-number"
                            dir="ltr"
                        >
                            {{ $account->account_number }}
                        </strong>

                    </div>


                    {{-- نوع حساب --}}
                    <div class="account-info-item">

                    <span class="account-info-label">
                        <i class="bi bi-wallet2"></i>
                        نوع حساب
                    </span>

                        <strong class="account-info-value">
                            {{ $account->account_type->label() }}
                        </strong>

                    </div>

                </div>


                {{-- موجودی --}}
                <div class="account-balance-card">

                    <div class="account-balance-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div class="account-balance-content">

                    <span class="account-balance-label">
                        موجودی فعلی
                    </span>

                        <div class="account-balance-value">

                            <strong>
                                {{ number_format($account->balance) }}
                            </strong>

                            <span>ریال</span>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- عملیات حساب --}}
        <div class="card account-actions-card">

            <div class="card-header account-info-header">

                <div class="account-section-title">

                <span class="account-section-icon">
                    <i class="bi bi-lightning-charge"></i>
                </span>

                    <span>
                    عملیات حساب
                </span>

                </div>

            </div>


            <div class="card-body">

                <div class="account-actions-grid">

                    {{-- واریز --}}
                    <a
                        href="{{ route('accounts.deposit.create', $account) }}"
                        class="account-action-btn deposit"
                    >

                    <span class="account-action-icon">
                        <i class="bi bi-arrow-up-circle"></i>
                    </span>

                        <span class="account-action-content">

                        <strong>
                            واریز
                        </strong>

                        <small>
                            ثبت واریز به حساب
                        </small>

                    </span>

                        <i class="bi bi-chevron-left account-action-arrow"></i>

                    </a>


                    {{-- برداشت --}}
                    <a
                        href="{{ route('accounts.withdrawal.create', $account) }}"
                        class="account-action-btn withdrawal"
                    >

                    <span class="account-action-icon">
                        <i class="bi bi-arrow-down-circle"></i>
                    </span>

                        <span class="account-action-content">

                        <strong>
                            برداشت از حساب
                        </strong>

                        <small>
                            ثبت برداشت از حساب
                        </small>

                    </span>

                        <i class="bi bi-chevron-left account-action-arrow"></i>

                    </a>


                    {{-- گردش حساب --}}
                    <a
                        href="{{ route('accounts.transactions', $account) }}"
                        class="account-action-btn transactions"
                    >

                    <span class="account-action-icon">
                        <i class="bi bi-list-ul"></i>
                    </span>

                        <span class="account-action-content">

                        <strong>
                            گردش حساب
                        </strong>

                        <small>
                            مشاهده تراکنش‌های حساب
                        </small>

                    </span>

                        <i class="bi bi-chevron-left account-action-arrow"></i>

                    </a>


                    {{-- اصلاح موجودی --}}
                    <a
                        href="{{ route('accounts.adjustment.create', $account) }}"
                        class="account-action-btn adjustment"
                    >

                    <span class="account-action-icon">
                        <i class="bi bi-pencil-square"></i>
                    </span>

                        <span class="account-action-content">

                        <strong>
                            اصلاح موجودی
                        </strong>

                        <small>
                            اصلاح دستی موجودی حساب
                        </small>

                    </span>

                        <i class="bi bi-chevron-left account-action-arrow"></i>

                    </a>

                </div>

            </div>

        </div>

    </div>

@endsection
