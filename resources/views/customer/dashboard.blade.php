@extends('customer.layouts.app')

@section('title', 'داشبورد مشتری')

@section('content')

    <div class="customer-dashboard">

        {{-- =========================================================
     اعلان‌های مشتری
     اطلاعات از Notification خوانده می‌شود
     ========================================================= --}}

        @if($notifications->isNotEmpty())

            @foreach($notifications as $notification)

                {{-- =====================================================
                    اعلان تأیید وام
                    ===================================================== --}}

                @if($notification->type === 'loan_request_approved')

                    <div class="loan-request-notice loan-request-notice-approved">

                        <div class="loan-request-notice-icon">
                            <i class="bi bi-check-lg"></i>
                        </div>

                        <div class="loan-request-notice-content">

                            <div class="loan-request-notice-title">
                                {{ $notification->title }}
                            </div>

                            <div class="loan-request-notice-text">
                                {{ $notification->message }}
                            </div>

                            @if(is_array($notification->data))

                                <div class="loan-request-notice-details">

                                    {{-- مبلغ وام --}}
                                    @if(isset($notification->data['approved_amount']))

                                        <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ وام
                                    </span>

                                            <strong>
                                                {{ number_format($notification->data['approved_amount']) }}
                                                ریال
                                            </strong>

                                        </div>

                                    @endif

                                    {{-- تعداد اقساط --}}
                                    @if(isset($notification->data['approved_installment_count']))

                                        <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        تعداد اقساط
                                    </span>

                                            <strong>
                                                {{ $notification->data['approved_installment_count'] }}
                                                قسط
                                            </strong>

                                        </div>

                                    @endif

                                    {{-- دوره بازپرداخت --}}
                                    @if(isset($notification->data['approved_installment_interval']))

                                        <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        دوره بازپرداخت
                                    </span>

                                            <strong>

                                                @switch($notification->data['approved_installment_interval'])

                                                    @case(1)
                                                    ماهانه
                                                    @break

                                                    @case(2)
                                                    هر دو ماه
                                                    @break

                                                    @case(3)
                                                    هر سه ماه
                                                    @break

                                                    @default
                                                    ---
                                                @endswitch

                                            </strong>

                                        </div>

                                    @endif

                                </div>

                                {{-- پیام مدیر --}}
                                @if(!empty($notification->data['review_note']))

                                    <div class="loan-request-notice-note">

                                        <i class="bi bi-chat-left-text"></i>

                                        <span>
                                    {{ $notification->data['review_note'] }}
                                </span>

                                    </div>

                                @endif

                            @endif

                            {{-- تاریخ اعلان --}}
                            <div class="loan-request-notice-note">

                                <i class="bi bi-calendar-event"></i>

                                <span>
                            {{ jdate($notification->created_at)->format('Y/m/d H:i') }}
                        </span>

                            </div>

                        </div>

                    </div>


                    {{-- =====================================================
                        اعلان رد وام
                        ===================================================== --}}

                @elseif($notification->type === 'loan_request_rejected')

                    <div class="loan-request-notice loan-request-notice-rejected">

                        <div class="loan-request-notice-icon">
                            <i class="bi bi-x-lg"></i>
                        </div>

                        <div class="loan-request-notice-content">

                            <div class="loan-request-notice-title">
                                {{ $notification->title }}
                            </div>

                            <div class="loan-request-notice-text">
                                {{ $notification->message }}
                            </div>

                            @if(is_array($notification->data))

                                <div class="loan-request-notice-details">

                                    {{-- مبلغ درخواستی --}}
                                    @if(isset($notification->data['requested_amount']))

                                        <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ درخواستی
                                    </span>

                                            <strong>
                                                {{ number_format($notification->data['requested_amount']) }}
                                                ریال
                                            </strong>

                                        </div>

                                    @endif

                                </div>

                                {{-- پیام مدیر --}}
                                @if(!empty($notification->data['review_note']))

                                    <div class="loan-request-notice-note">

                                        <i class="bi bi-chat-left-text"></i>

                                        <span>
                                    {{ $notification->data['review_note'] }}
                                </span>

                                    </div>

                                @endif

                                {{-- تاریخ مراجعه مجدد --}}
                                @if(!empty($notification->data['next_review_date']))

                                    <div class="loan-request-notice-next-date">

                                        <i class="bi bi-calendar-event"></i>

                                        <span>
                                    تاریخ مراجعه مجدد:
                                </span>

                                        <strong>
                                            {{ $notification->data['next_review_date'] }}
                                        </strong>

                                    </div>

                                @endif

                            @endif

                            {{-- تاریخ اعلان --}}
                            <div class="loan-request-notice-note">

                                <i class="bi bi-calendar-event"></i>

                                <span>
                            {{ jdate($notification->created_at)->format('Y/m/d H:i') }}
                        </span>

                            </div>

                        </div>

                    </div>


                    blade
                    {{-- =====================================================
                        اعلان وام واریز شده
                        ===================================================== --}}

                @elseif($notification->type === 'loan_disbursed')

                    <div class="loan-request-notice loan-request-notice-approved">

                        {{-- آیکون --}}
                        <div class="loan-request-notice-icon">
                            <i class="bi bi-wallet2"></i>
                        </div>


                        <div class="loan-request-notice-content">

                            {{-- عنوان --}}
                            <div class="loan-request-notice-title">
                                {{ $notification->title }}
                            </div>


                            {{-- پیام --}}
                            @if($notification->message)

                                <div class="loan-request-notice-text">
                                    {{ $notification->message }}
                                </div>

                            @endif


                            {{-- اطلاعات وام --}}
                            @if(is_array($notification->data))

                                <div class="loan-request-notice-details">

                                    @if(isset($notification->data['loan_amount']))

                                        <div class="loan-request-detail">

                            <span class="loan-request-detail-label">
                                مبلغ وام
                            </span>

                                            <strong>
                                                {{ number_format($notification->data['loan_amount']) }}
                                                ریال
                                            </strong>

                                        </div>

                                    @endif

                                </div>

                            @endif


                            {{-- توضیح برداشت --}}
                            <div class="loan-request-notice-note">

                                <i class="bi bi-info-circle"></i>

                                <span>
                    مبلغ وام به حساب پس‌انداز شما واریز شده است.
                    برای دریافت وجه، درخواست برداشت ثبت کنید.
                </span>

                            </div>


                            {{-- =================================================
                                دکمه برداشت
                                ================================================= --}}

                            <div class="loan-notification-action mt-3">

                                <a href="{{ route('customer.savings.withdrawal.create') }}"
                                   class="loan-notification-withdraw-btn">

                    <span class="loan-notification-withdraw-icon">
                        <i class="bi bi-wallet2"></i>
                    </span>

                                    <span class="loan-notification-withdraw-content">

                        <strong>
                            درخواست برداشت وجه
                        </strong>

                        <small>
                            برای دریافت مبلغ وام، درخواست برداشت ثبت کنید.
                        </small>

                    </span>

                                    <i class="bi bi-chevron-left loan-notification-withdraw-arrow"></i>

                                </a>

                            </div>


                            {{-- تاریخ اعلان --}}
                            <div class="loan-request-notice-note mt-3">

                                <i class="bi bi-calendar-event"></i>

                                <span>
                    {{ jdate($notification->created_at)->format('Y/m/d H:i') }}
                </span>

                            </div>

                        </div>

                    </div>


                    {{-- =====================================================
                        سایر اعلان‌ها
                        ===================================================== --}}

                @else



                    <div class="loan-request-notice">

                        <div class="loan-request-notice-icon">
                            <i class="bi bi-bell"></i>
                        </div>

                        <div class="loan-request-notice-content">

                            <div class="loan-request-notice-title">
                                {{ $notification->title }}
                            </div>

                            @if($notification->message)

                                <div class="loan-request-notice-text">
                                    {{ $notification->message }}
                                </div>

                            @endif

                            {{-- تاریخ اعلان --}}
                            <div class="loan-request-notice-note mt-3">

                                <i class="bi bi-calendar-event"></i>

                                <span>
                            {{ jdate($notification->created_at)->format('Y/m/d H:i') }}
                        </span>

                            </div>

                        </div>

                    </div>

                @endif

            @endforeach

        @endif




        {{-- =========================================================
     وضعیت مالی من
     ========================================================= --}}
        <section class="customer-financial-section">

            <div class="customer-financial-section-header">

                <div>
                    <h2>وضعیت مالی من</h2>

                    <span>
                خلاصه حساب و وام
            </span>
                </div>

                <span class="customer-financial-section-status">
            <i class="bi bi-check-circle-fill"></i>
            فعال
        </span>

            </div>


            <div class="customer-financial-cards">

                {{-- =====================================================
                     مجموع موجودی
                     ===================================================== --}}
                <div class="customer-financial-card-item total">

                    <div class="customer-financial-card-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div class="customer-financial-card-content">

                <span class="customer-financial-card-label">
                    مجموع موجودی
                </span>

                        <strong class="customer-financial-card-value">
                            {{ number_format($totalBalance) }}
                            <small>ریال</small>
                        </strong>

                    </div>

                </div>


                {{-- =====================================================
                     حساب پس‌انداز
                     ===================================================== --}}
                <div class="customer-financial-card-item savings">

                    <div class="customer-financial-card-icon">
                        <i class="bi bi-piggy-bank-fill"></i>
                    </div>

                    <div class="customer-financial-card-content">

                <span class="customer-financial-card-label">
                    پس‌انداز
                </span>

                        @if($savingsAccount)

                            <strong class="customer-financial-card-value">
                                {{ number_format($savingsAccount->balance) }}
                                <small>ریال</small>
                            </strong>

                            <span class="customer-financial-card-meta">
                        حساب {{ $savingsAccount->account_number }}
                    </span>

                        @else

                            <strong class="customer-financial-card-value muted">
                                بدون حساب
                            </strong>

                        @endif

                    </div>

                </div>


                {{-- =====================================================
                     وام فعال
                     ===================================================== --}}
                <div class="customer-financial-card-item loan">

                    <div class="customer-financial-card-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>

                    <div class="customer-financial-card-content">

                <span class="customer-financial-card-label">
                    وام فعال
                </span>

                        @if($activeLoan)

                            <strong class="customer-financial-card-value">
                                {{ number_format($activeLoan->remainingAmount()) }}
                                <small>ریال باقی‌مانده</small>
                            </strong>

                            <span class="customer-financial-card-meta">
                        وام {{ $activeLoan->full_loan_number }}
                    </span>

                        @else

                            <strong class="customer-financial-card-value muted">
                                بدون وام فعال
                            </strong>

                        @endif

                    </div>

                </div>


                {{-- =====================================================
                     اقساط معوق
                     ===================================================== --}}
                <div class="customer-financial-card-item overdue">

                    <div class="customer-financial-card-icon">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>

                    <div class="customer-financial-card-content">

                <span class="customer-financial-card-label">
                    اقساط معوق
                </span>

                        @if($overdueInstallmentsCount > 0)

                            <strong class="customer-financial-card-value danger">
                                {{ $overdueInstallmentsCount }}
                                <small>قسط</small>
                            </strong>

                            <span class="customer-financial-card-meta danger-text">
                        نیاز به پرداخت
                    </span>

                        @else

                            <strong class="customer-financial-card-value success">
                                بدون معوقه
                            </strong>

                            <span class="customer-financial-card-meta success-text">
                        پرداخت منظم است
                    </span>

                        @endif

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
                <a href="{{ route('customer.loan-request.create') }}"
                   class="customer-quick-action">

    <span class="customer-quick-action-icon">
        <i class="bi bi-folder-plus"></i>
    </span>

                    <span class="customer-quick-action-text">
        درخواست
        وام
    </span>

                </a>
                {{-- درخواست‌های وام من --}}
                <a href="{{ url('/customer/loan-requests') }}"
                   class="customer-quick-action">

    <span class="customer-quick-action-icon">
        <i class="bi bi-list-check"></i>
    </span>

                    <span class="customer-quick-action-text">
        درخواست‌های
        وام من
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
