
@extends('customer.layouts.app')

@section('title', 'خدمات')

@section('content')

    <div class="customer-services">

        {{-- Header --}}
        <div class="services-header">
            <div>
                <h1 class="services-title">خدمات</h1>
                <p class="services-subtitle">
                    خدمات و امکانات حساب کاربری شما
                </p>
            </div>

            <div class="services-header-icon">
                <i class="bi bi-grid-1x2"></i>
            </div>
        </div>


        {{-- Accounts --}}
        <section class="services-section">

            <div class="services-section-title">
                <div class="section-title-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div>
                    <h2>حساب‌های من</h2>
                    <span>مدیریت حساب‌ها و موجودی</span>
                </div>
            </div>


            <div class="services-grid">

                {{-- Savings Account --}}
                @if($savingsAccount)

                    <div class="service-card account-card savings-card">

                        <div class="service-card-top">

                            <div class="service-icon">
                                <i class="bi bi-piggy-bank"></i>
                            </div>

                            <span class="service-badge">
                            {{ $savingsAccount->status->label() }}
                        </span>

                        </div>

                        <div class="service-card-body">

                            <h3>حساب پس‌انداز</h3>

                            <div class="account-number">
                                {{ $savingsAccount->account_number }}
                            </div>

                            <div class="account-balance">
                                <span>موجودی</span>

                                <strong>
                                    {{ number_format($savingsAccount->balance) }}
                                    <small>ریال</small>
                                </strong>
                            </div>

                        </div>

                        <div class="service-card-actions">

                            <a href="{{ route('customer.savings.transactions') }}"
                               class="service-action primary">
                                <i class="bi bi-arrow-left"></i>
                                تراکنش‌ها
                            </a>

                            <a href="{{ route('customer.savings.deposit.create') }}"
                               class="service-action">
                                واریز
                            </a>

                        </div>

                    </div>

                @endif


                {{-- Current Account --}}
                @if($currentAccount)

                    <div class="service-card account-card current-card">

                        <div class="service-card-top">

                            <div class="service-icon">
                                <i class="bi bi-bank"></i>
                            </div>

                            <span class="service-badge">
                            {{ $currentAccount->status->label() }}
                        </span>

                        </div>

                        <div class="service-card-body">

                            <h3>حساب جاری</h3>

                            <div class="account-number">
                                {{ $currentAccount->account_number }}
                            </div>

                            <div class="account-balance">
                                <span>موجودی</span>

                                <strong>
                                    {{ number_format($currentAccount->balance) }}
                                    <small>ریال</small>
                                </strong>
                            </div>

                        </div>

                        <div class="service-card-actions">

                            <a href="{{ route('accounts.show', $currentAccount) }}"
                               class="service-action primary">
                                <i class="bi bi-arrow-left"></i>
                                مشاهده حساب
                            </a>

                            <a href="{{ route('accounts.transactions', $currentAccount) }}"
                               class="service-action">
                                تراکنش‌ها
                            </a>

                        </div>

                    </div>

                @endif

            </div>

        </section>


        {{-- Loans --}}
        <section class="services-section">

            <div class="services-section-title">

                <div class="section-title-icon">
                    <i class="bi bi-cash-coin"></i>
                </div>

                <div>
                    <h2>وام و اقساط</h2>
                    <span>مدیریت و پرداخت تسهیلات</span>
                </div>

            </div>


            <div class="services-grid">


                {{-- My Loans --}}
                <a href="{{ route('customer.loans.index') }}"
                   class="service-card feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div class="feature-content">

                        <h3>وام‌های من</h3>

                        <p>
                             مدیریت وام‌های فعال و گذشته
                        </p>

                    </div>

                    <i class="bi bi-chevron-left feature-arrow"></i>

                </a>


                {{-- Loan Requests --}}
                <a href="{{ route('customer.loan-requests.index') }}"
                   class="service-card feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div class="feature-content">

                        <h3>درخواست‌های وام</h3>

                        <p>
                             وضعیت درخواست‌های وام
                        </p>

                    </div>

                    <i class="bi bi-chevron-left feature-arrow"></i>

                </a>


                {{-- Installments --}}
                <a href="{{ route('customer.installments.index') }}"
                   class="service-card feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div class="feature-content">

                        <h3>اقساط من</h3>

                        <p>
                             اقساط و پرداخت‌های انجام‌شده
                        </p>

                    </div>

                    <i class="bi bi-chevron-left feature-arrow"></i>

                </a>


                {{-- Pay Other Installment --}}
                <a href="{{ route('customer.installments.others.create') }}"
                   class="service-card feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-person-check"></i>
                    </div>

                    <div class="feature-content">

                        <h3>پرداخت قسط دیگران</h3>

                        <p>
                            پرداخت قسط با شماره وام
                        </p>

                    </div>

                    <i class="bi bi-chevron-left feature-arrow"></i>

                </a>

            </div>

        </section>


        {{-- Financial Services --}}
        <section class="services-section">

            <div class="services-section-title">

                <div class="section-title-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </div>

                <div>
                    <h2>خدمات مالی</h2>
                    <span>واریز، برداشت و انتقال</span>
                </div>

            </div>


            <div class="services-grid">


                {{-- Deposit --}}
                <a href="{{ route('customer.savings.deposit.create') }}"
                   class="service-card feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-plus-circle"></i>
                    </div>

                    <div class="feature-content">

                        <h3>واریز به حساب</h3>

                        <p>
                            واریز وجه به حساب پس‌انداز
                        </p>

                    </div>

                    <i class="bi bi-chevron-left feature-arrow"></i>

                </a>


                {{-- Withdrawal --}}
                <a href="{{ route('customer.savings.withdrawal.create') }}"
                   class="service-card feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-dash-circle"></i>
                    </div>

                    <div class="feature-content">

                        <h3>برداشت از حساب</h3>

                        <p>
                            درخواست برداشت از حساب پس‌انداز
                        </p>

                    </div>

                    <i class="bi bi-chevron-left feature-arrow"></i>

                </a>


                {{-- Transfer --}}
                <a href="{{ route('customer.savings-transfer.create') }}"
                   class="service-card feature-card">

                    <div class="feature-icon">
                        <i class="bi bi-send"></i>
                    </div>

                    <div class="feature-content">

                        <h3>انتقال وجه</h3>

                        <p>
                            انتقال وجه به حساب پس‌انداز دیگران
                        </p>

                    </div>

                    <i class="bi bi-chevron-left feature-arrow"></i>

                </a>

            </div>

        </section>

    </div>

@endsection

