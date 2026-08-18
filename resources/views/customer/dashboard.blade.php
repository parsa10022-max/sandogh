@extends('customer.layouts.app')

@section('title', 'داشبورد مشتری')

@section('content')

    <div class="customer-dashboard">

        {{-- =====================================================
             وضعیت مالی من
             ===================================================== --}}
        <section class="customer-financial-card">

            {{-- آیکون بزرگ مالی --}}
            <div class="customer-financial-visual">
                <div class="customer-financial-big-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <span>وضعیت مالی</span>
            </div>


            {{-- اطلاعات مالی --}}
            <div class="customer-financial-main">

                <div class="customer-financial-header">

                    <div class="customer-financial-title">
                        <h2>وضعیت مالی من</h2>
                    </div>

                    <span class="customer-financial-status">
                <i class="bi bi-check-circle-fill"></i>
                فعال
            </span>

                </div>


                <div class="customer-financial-grid">

                    <div class="customer-financial-item">
                <span class="customer-financial-label">
                    حساب پس‌انداز
                </span>

                        <strong class="customer-financial-value customer-financial-account-number">
                            {{ $savingsAccount->account_number ?? '۶۱۱۱-۹۱۱۴' }}
                        </strong>
                    </div>


                    <div class="customer-financial-item">
                <span class="customer-financial-label">
                    موجودی
                </span>

                        <strong class="customer-financial-value customer-financial-balance">
                            {{ number_format($savingsAccount->balance ?? 50000000) }}
                            <small>ریال</small>
                        </strong>
                    </div>


                    <div class="customer-financial-item customer-financial-iban">
                <span class="customer-financial-label">
                    شماره شبا
                </span>

                        <strong class="customer-financial-value customer-financial-iban-value">
                            IR ۱۲۳۴ ۵۶۷۸ ۹۰۱۲ ۳۴۵۶ ۷۸۹۰ ۱۲۳۴
                        </strong>
                    </div>


                    <div class="customer-financial-item">
                <span class="customer-financial-label">
                    بانک
                </span>

                        <strong class="customer-financial-value">
                            کشاورزی
                        </strong>
                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
             عملیات سریع
             مرحله بعد تکمیل می‌شود
             ===================================================== --}}


        <section class="customer-quick-actions-section">

            <div class="customer-quick-actions-title">
                <h2>عملیات سریع</h2>
            </div>

            <div class="customer-quick-actions">

                <a href="{{ route('customer.savings.deposit.create') }}"
                   class="customer-quick-action">

    <span class="customer-quick-action-icon">
        <i class="bi bi-plus-circle-fill"></i>
    </span>

                    <span class="customer-quick-action-text">
        واریز به
        پس‌انداز
    </span>

                </a>


                <a href="{{ route('customer.savings.withdrawal.create') }}"
                   class="customer-quick-action">

    <span class="customer-quick-action-icon">
        <i class="bi bi-arrow-return-left"></i>
    </span>

                    <span class="customer-quick-action-text">
        برداشت از
        پس‌انداز
    </span>

                </a>

                {{-- گردش حساب --}}
                <a href="{{ route('customer.savings.transactions') }}"
                   class="customer-quick-action">

    <span class="customer-quick-action-icon">
        <i class="bi bi-receipt"></i>
    </span>

                    <span class="customer-quick-action-text">
        گردش
        حساب
    </span>

                </a>


                {{-- درخواست وام --}}
                <a href="{{ route('loan-requests.create') }}"
                   class="customer-quick-action">

    <span class="customer-quick-action-icon">
        <i class="bi bi-folder-plus"></i>
    </span>

                    <span class="customer-quick-action-text">
        درخواست
        وام
    </span>

                </a>


                <a href="{{ route('customer.installments.index') }}"
                   class="customer-quick-action">
        <span class="customer-quick-action-icon">
            <i class="bi bi-journal-text"></i>
        </span>

                    <span class="customer-quick-action-text">
            پرداخت
            قسط
        </span>
                </a>


                <a href="{{ route('customer.installments.others.create') }}"
                   class="customer-quick-action">
        <span class="customer-quick-action-icon">
            <i class="bi bi-people-fill"></i>
        </span>

                    <span class="customer-quick-action-text">
            پرداخت قسط
            دیگران
        </span>
                </a>

            </div>

        </section>

        {{-- =========================================================
     DASHBOARD - LOAN & TRANSACTIONS
     ========================================================= --}}

        <div class="customer-dashboard-bottom-grid">

            {{-- =====================================================
                 آخرین تراکنش‌ها
                 ===================================================== --}}
            <section class="customer-transactions-section">

                <div class="customer-transactions-header">

                    <h2>
                        آخرین تراکنش‌ها
                    </h2>

                    <a href="#" class="customer-transactions-all">
                        مشاهده همه
                        <i class="bi bi-arrow-left"></i>
                    </a>

                </div>


                <div class="customer-transactions-table-wrapper">

                    <table class="customer-transactions-table">

                        <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>نوع تراکنش</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                        </tr>
                        </thead>

                        <tbody>

                        {{-- واریز --}}
                        <tr>

                            <td>۱۴۰۵/۰۲/۲۸</td>

                            <td>
                                <div class="customer-transaction-type">

                            <span class="customer-transaction-icon deposit">
                                <i class="bi bi-arrow-down"></i>
                            </span>

                                    <span>
                                واریز به پس‌انداز
                            </span>

                                </div>
                            </td>

                            <td class="customer-transaction-amount positive">
                                ۵,۰۰۰,۰۰۰ ریال
                            </td>

                            <td>
                        <span class="customer-transaction-status success">
                            موفق
                        </span>
                            </td>

                        </tr>


                        {{-- پرداخت قسط --}}
                        <tr>

                            <td>۱۴۰۵/۰۲/۲۵</td>

                            <td>
                                <div class="customer-transaction-type">

                            <span class="customer-transaction-icon payment">
                                <i class="bi bi-arrow-up"></i>
                            </span>

                                    <span>
                                پرداخت قسط وام
                            </span>

                                </div>
                            </td>

                            <td class="customer-transaction-amount negative">
                                ۲,۵۰۰,۰۰۰ ریال
                            </td>

                            <td>
                        <span class="customer-transaction-status success">
                            موفق
                        </span>
                            </td>

                        </tr>


                        {{-- برداشت --}}
                        <tr>

                            <td>۱۴۰۵/۰۲/۲۰</td>

                            <td>
                                <div class="customer-transaction-type">

                            <span class="customer-transaction-icon withdrawal">
                                <i class="bi bi-arrow-left"></i>
                            </span>

                                    <span>
                                برداشت از پس‌انداز
                            </span>

                                </div>
                            </td>

                            <td class="customer-transaction-amount negative">
                                ۱,۰۰۰,۰۰۰ ریال
                            </td>

                            <td>
                        <span class="customer-transaction-status success">
                            موفق
                        </span>
                            </td>

                        </tr>


                        {{-- واریز --}}
                        <tr>

                            <td>۱۴۰۵/۰۲/۱۸</td>

                            <td>
                                <div class="customer-transaction-type">

                            <span class="customer-transaction-icon deposit">
                                <i class="bi bi-arrow-down"></i>
                            </span>

                                    <span>
                                واریز به پس‌انداز
                            </span>

                                </div>
                            </td>

                            <td class="customer-transaction-amount positive">
                                ۳,۰۰۰,۰۰۰ ریال
                            </td>

                            <td>
                        <span class="customer-transaction-status success">
                            موفق
                        </span>
                            </td>

                        </tr>

                        </tbody>

                    </table>

                </div>

            </section>


            {{-- =====================================================
                 وام فعال شما
                 ===================================================== --}}
            <section class="customer-active-loan-card">

                <div class="customer-active-loan-header">

                    <div class="customer-active-loan-title">

                <span class="customer-active-loan-title-icon">
                    <i class="bi bi-cash-coin"></i>
                </span>

                        <h2>
                            وام فعال شما
                        </h2>

                    </div>

                    <span class="customer-active-loan-status">
                فعال
            </span>

                </div>


                <div class="customer-active-loan-name">
                    وام قرض‌الحسنه
                </div>


                <div class="customer-loan-progress-row">

                    <div class="customer-loan-progress">

                        <div class="customer-loan-progress-bar"
                             style="width: 60%;">
                        </div>

                    </div>

                    <span class="customer-loan-progress-value">
                ۶۰٪
            </span>

                </div>


                <div class="customer-active-loan-info">

                    <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    مبلغ وام
                </span>

                        <strong class="customer-loan-info-value">
                            ۱۰۰,۰۰۰,۰۰۰ ریال
                        </strong>

                    </div>


                    <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    باقی‌مانده
                </span>

                        <strong class="customer-loan-info-value">
                            ۴۰,۰۰۰,۰۰۰ ریال
                        </strong>

                    </div>


                    <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    اقساط باقی‌مانده
                </span>

                        <strong class="customer-loan-info-value">
                            ۴ قسط
                        </strong>

                    </div>


                    <div class="customer-loan-info-row">

                <span class="customer-loan-info-label">
                    قسط بعدی
                </span>

                        <strong class="customer-loan-info-value">
                            ۱۴۰۵/۰۳/۰۵
                        </strong>

                    </div>

                </div>


                <a href="#"
                   class="customer-active-loan-details-button">

                    مشاهده جزئیات وام

                    <i class="bi bi-arrow-left"></i>

                </a>

            </section>

        </div>


        {{-- =========================================================
     کمک‌ها
     ========================================================= --}}
        <section class="customer-donations-section">

            <div class="customer-donations-title">
                <h2>کمک‌ها</h2>
            </div>


            <div class="customer-donations-actions">

                @forelse($donationAccounts as $account)

                    <a href="{{ route('customer.donations.create', ['account_id' => $account->id]) }}"
                       class="customer-donation-action text-decoration-none">

                <span class="customer-donation-action-icon">

                    <i class="bi bi-heart-fill"></i>

                </span>


                        <span class="customer-donation-action-text">

                    {{ $account->name }}

                </span>

                    </a>

                @empty

                    <div class="customer-donations-empty">

                        <i class="bi bi-heart"></i>

                        <span>
                    در حال حاضر گزینه‌ای برای کمک فعال نیست.
                </span>

                    </div>

                @endforelse

            </div>

        </section>


    </div>

@endsection
