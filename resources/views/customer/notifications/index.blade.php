@extends('customer.layouts.app')

@section('title', 'اعلان‌ها')

@section('header_title', 'اعلان‌ها')

@section('header_subtitle', 'پیام‌ها و اعلان‌های شما')

@section('content')


    @php
        /*
         |--------------------------------------------------------------------------
         | مرتب‌سازی اقساط معوق
         |--------------------------------------------------------------------------
         */

        $sortedOverdueInstallments = collect($overdueInstallments ?? [])
            ->sortBy('installment_number')
            ->values();

        /*
         |--------------------------------------------------------------------------
         | اولین قسط قابل پرداخت
         |--------------------------------------------------------------------------
         |
         | فقط اولین قسط پرداخت‌نشده باید دکمه پرداخت داشته باشد.
         |
         */

        $firstPayableInstallment = $sortedOverdueInstallments->first();

        $firstPayableInstallmentId = $firstPayableInstallment?->id;

        /*
         |--------------------------------------------------------------------------
         | حذف اعلان‌های تکراری اقساط معوق
         |--------------------------------------------------------------------------
         |
         | اقساط معوق در کارت بالای صفحه نمایش داده می‌شوند،
         | بنابراین اعلان‌های overdue_installment را از لیست اعلان‌ها
         | حذف می‌کنیم تا اطلاعات تکراری نمایش داده نشود.
         |
         */

        $notificationCollection = $notifications->getCollection();

        $sortedNotifications = $notificationCollection
            ->reject(function ($notification) {
                return $notification->type === 'overdue_installment';
            })
            ->values();
    @endphp


    <div class="customer-notifications">

        {{-- =========================================================
             تعداد اعلان‌های خوانده‌نشده
        ========================================================== --}}

        <div class="text-muted small mb-3">

            @if($unreadCount > 0)

                {{ $unreadCount }} اعلان جدید برای شما ثبت شده بود.

            @else

                همه اعلان‌ها قبلاً مشاهده شده‌اند.

            @endif

        </div>


        {{-- =========================================================
             هشدار اقساط معوق
        ========================================================== --}}

        @if($overdueCount > 0)

            <div class="loan-overdue-notice mb-4">

                <div class="loan-overdue-notice-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>

                <div class="loan-overdue-notice-content">

                    {{-- عنوان --}}

                    <div class="loan-overdue-notice-title">
                        اقساط معوق دارید
                    </div>


                    {{-- توضیح --}}

                    <div class="loan-overdue-notice-text">

                        شما
                        <strong>{{ $overdueCount }}</strong>
                        قسط پرداخت‌نشده دارید که از تاریخ سررسید آن گذشته است.

                    </div>


                    {{-- =================================================
                         خلاصه اقساط معوق
                    ================================================== --}}

                    <div class="loan-overdue-notice-summary">

                        <div class="loan-overdue-summary-item">

                        <span>
                            تعداد اقساط معوق
                        </span>

                            <strong>
                                {{ $overdueCount }}
                            </strong>

                        </div>


                        <div class="loan-overdue-summary-item">

                        <span>
                            مبلغ کل معوق
                        </span>

                            <strong>
                                {{ number_format($overdueAmount) }}
                                ریال
                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         لیست اقساط معوق
                    ================================================== --}}

                    <div class="loan-overdue-installments">

                        @foreach($sortedOverdueInstallments as $installment)

                            @php
                                /*
                                 * فقط اولین قسط معوق قابل پرداخت است.
                                 */
                                $canPayInstallment =
                                    $installment->id === $firstPayableInstallmentId;
                            @endphp


                            <div class="loan-overdue-installment">

                                {{-- اطلاعات اصلی قسط --}}

                                <div class="loan-overdue-installment-main">

                                    <strong>
                                        قسط شماره
                                        {{ $installment->installment_number }}
                                    </strong>

                                    @if($installment->loan?->loanType)

                                        <small>
                                            {{ $installment->loan->loanType->name }}
                                        </small>

                                    @endif

                                </div>


                                {{-- اطلاعات قسط --}}

                                <div class="loan-overdue-installment-info">

                                    <div>

                                    <span>
                                        مبلغ
                                    </span>

                                        <strong>
                                            {{ number_format($installment->amount) }}
                                            ریال
                                        </strong>

                                    </div>


                                    <div>

                                    <span>
                                        سررسید
                                    </span>

                                        <strong>
                                            {{ $installment->due_date_jalali }}
                                        </strong>

                                    </div>


                                    <div>

                                    <span>
                                        تأخیر
                                    </span>

                                        <strong>
                                            {{ $installment->overdue_days }}
                                            روز
                                        </strong>

                                    </div>

                                </div>


                                {{-- =================================================
                                     دکمه پرداخت
                                ================================================== --}}

                                @if($canPayInstallment)

                                    <div class="loan-notification-action mt-3">

                                        <form
                                            action="{{ route('payments.pay', $installment->id) }}"
                                            method="POST"
                                        >

                                            @csrf

                                            <button
                                                type="submit"
                                                class="loan-notification-pay-btn"
                                            >

                                            <span class="loan-notification-pay-icon">
                                                <i class="bi bi-credit-card"></i>
                                            </span>

                                                <span class="loan-notification-pay-content">

                                                <strong>
                                                    پرداخت  قسط
                                                </strong>


                                            </span>

                                                <i class="bi bi-chevron-left loan-notification-pay-arrow"></i>

                                            </button>

                                        </form>

                                    </div>

                                @endif

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        @endif


        {{-- =========================================================
             لیست سایر اعلان‌ها
        ========================================================== --}}

        @if($sortedNotifications->isNotEmpty())

            @foreach($sortedNotifications as $notification)

                @php

                    $data = is_array($notification->data)
                        ? $notification->data
                        : [];

                    $amount = $data['amount'] ?? null;

                    $accountNumber = $data['account_number']
                        ?? $data['destination_account_number']
                        ?? null;

                    $trackingCode = $data['tracking_code']
                        ?? null;

                    $receiverName = $data['receiver_name']
                        ?? $data['customer_name']
                        ?? $data['destination_customer_name']
                        ?? null;

                    $installmentNumber = $data['installment_number']
                        ?? null;

                @endphp


                <div class="loan-request-notice">

                    {{-- =================================================
                         آیکون اعلان
                    ================================================== --}}

                    <div class="loan-request-notice-icon">

                        @switch($notification->type)

                            @case('loan_request_approved')

                            <i class="bi bi-check-lg"></i>

                            @break


                            @case('loan_request_rejected')

                            <i class="bi bi-x-lg"></i>

                            @break


                            @case('loan_disbursed')

                            <i class="bi bi-wallet2"></i>

                            @break


                            @case('savings_deposit_success')

                            <i class="bi bi-arrow-down-circle"></i>

                            @break


                            @case('savings_deposit_other')

                            <i class="bi bi-arrow-down-circle"></i>

                            @break


                            @case('savings_transfer_success')

                            <i class="bi bi-arrow-left-right"></i>

                            @break


                            @case('savings_withdrawal_success')

                            <i class="bi bi-arrow-up-circle"></i>

                            @break


                            @case('installment_payment_success')

                            <i class="bi bi-calendar-check"></i>

                            @break


                            @case('other_installment_payment_success')

                            <i class="bi bi-person-check"></i>

                            @break


                            @case('loan_overdue')

                            <i class="bi bi-exclamation-triangle"></i>

                            @break


                            @default

                            <i class="bi bi-bell"></i>

                        @endswitch

                    </div>


                    {{-- =================================================
                         محتوای اعلان
                    ================================================== --}}

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


                        {{-- =================================================
                             واریز به حساب
                        ================================================== --}}

                        @if($notification->type === 'savings_deposit_success')

                            <div class="loan-request-notice-details">

                                @if($amount !== null)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ
                                    </span>

                                        <strong>
                                            {{ number_format((int) $amount) }} ریال
                                        </strong>

                                    </div>

                                @endif


                                @if($accountNumber)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        شماره حساب
                                    </span>

                                        <strong>
                                            {{ $accountNumber }}
                                        </strong>

                                    </div>

                                @endif


                                @if($trackingCode)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        کد پیگیری
                                    </span>

                                        <strong>
                                            {{ $trackingCode }}
                                        </strong>

                                    </div>

                                @endif

                            </div>

                        @endif


                        {{-- =================================================
                             واریز به حساب دیگران
                        ================================================== --}}

                        @if(
                            in_array($notification->type, [
                                'savings_deposit_other',
                                'savings_transfer_success'
                            ])
                        )

                            <div class="loan-request-notice-details">

                                @if($amount !== null)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ
                                    </span>

                                        <strong>
                                            {{ number_format((int) $amount) }} ریال
                                        </strong>

                                    </div>

                                @endif


                                @if($receiverName)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        واریز به
                                    </span>

                                        <strong>
                                            {{ $receiverName }}
                                        </strong>

                                    </div>

                                @endif


                                @if($accountNumber)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        شماره حساب مقصد
                                    </span>

                                        <strong>
                                            {{ $accountNumber }}
                                        </strong>

                                    </div>

                                @endif


                                @if($trackingCode)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        کد پیگیری
                                    </span>

                                        <strong>
                                            {{ $trackingCode }}
                                        </strong>

                                    </div>

                                @endif

                            </div>

                        @endif


                        {{-- =================================================
                             پرداخت قسط
                        ================================================== --}}

                        @if(
                            in_array($notification->type, [
                                'installment_payment_success',
                                'other_installment_payment_success'
                            ])
                        )

                            <div class="loan-request-notice-details">

                                @if($amount !== null)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ
                                    </span>

                                        <strong>
                                            {{ number_format((int) $amount) }} ریال
                                        </strong>

                                    </div>

                                @endif


                                @if($installmentNumber !== null)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        شماره قسط
                                    </span>

                                        <strong>
                                            {{ $installmentNumber }}
                                        </strong>

                                    </div>

                                @endif


                                @if($trackingCode)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        کد پیگیری
                                    </span>

                                        <strong>
                                            {{ $trackingCode }}
                                        </strong>

                                    </div>

                                @endif

                            </div>

                        @endif


                        {{-- =================================================
                             برداشت از حساب پس‌انداز
                        ================================================== --}}

                        @if($notification->type === 'savings_withdrawal_success')

                            <div class="loan-request-notice-details">

                                @if($amount !== null)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ برداشت
                                    </span>

                                        <strong>
                                            {{ number_format((int) $amount) }} ریال
                                        </strong>

                                    </div>

                                @endif


                                @if($accountNumber)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        شماره حساب
                                    </span>

                                        <strong>
                                            {{ $accountNumber }}
                                        </strong>

                                    </div>

                                @endif


                                @if($trackingCode)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        کد پیگیری
                                    </span>

                                        <strong>
                                            {{ $trackingCode }}
                                        </strong>

                                    </div>

                                @endif

                            </div>

                        @endif


                        {{-- =================================================
                             تأیید درخواست وام
                        ================================================== --}}

                        @if($notification->type === 'loan_request_approved')

                            <div class="loan-request-notice-details">

                                @if(isset($data['approved_amount']))

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ وام
                                    </span>

                                        <strong>
                                            {{ number_format((int) $data['approved_amount']) }}
                                            ریال
                                        </strong>

                                    </div>

                                @endif


                                @if(isset($data['approved_installment_count']))

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        تعداد اقساط
                                    </span>

                                        <strong>
                                            {{ $data['approved_installment_count'] }}
                                            قسط
                                        </strong>

                                    </div>

                                @endif


                                @if(isset($data['approved_installment_interval']))

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        دوره بازپرداخت
                                    </span>

                                        <strong>

                                            @switch((int) $data['approved_installment_interval'])

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

                            @if(!empty($data['review_note']))

                                <div class="loan-request-notice-note">

                                    <i class="bi bi-chat-left-text"></i>

                                    <span>
                                    پیام مدیر:
                                </span>

                                    <strong>
                                        {{ $data['review_note'] }}
                                    </strong>

                                </div>

                            @endif

                        @endif


                        {{-- =================================================
                             رد درخواست وام
                        ================================================== --}}

                        @if($notification->type === 'loan_request_rejected')

                            <div class="loan-request-notice-details">

                                @if(isset($data['requested_amount']))

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ درخواستی
                                    </span>

                                        <strong>
                                            {{ number_format((int) $data['requested_amount']) }}
                                            ریال
                                        </strong>

                                    </div>

                                @endif


                                @if(!empty($data['next_review_date']))

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        تاریخ مراجعه مجدد
                                    </span>

                                        <strong>
                                            {{ $data['next_review_date'] }}
                                        </strong>

                                    </div>

                                @endif

                            </div>


                            {{-- پیام مدیر --}}

                            @if(!empty($data['review_note']))

                                <div class="loan-request-notice-note">

                                    <i class="bi bi-chat-left-text"></i>

                                    <span>
                                    پیام مدیر:
                                </span>

                                    <strong>
                                        {{ $data['review_note'] }}
                                    </strong>

                                </div>

                            @endif

                        @endif


                        {{-- =================================================
                             وام واریز شده
                        ================================================== --}}

                        @if($notification->type === 'loan_disbursed')

                            @if($amount !== null)

                                <div class="loan-request-notice-details">

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ وام
                                    </span>

                                        <strong>
                                            {{ number_format((int) $amount) }}
                                            ریال
                                        </strong>

                                    </div>

                                </div>

                            @endif


                            <div class="loan-notification-action mt-3">

                                <a
                                    href="{{ route('customer.savings.withdrawal.create') }}"
                                    class="loan-notification-withdraw-btn"
                                >

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

                        @endif


                        {{-- =================================================
                             وام معوق
                        ================================================== --}}

                        @if($notification->type === 'loan_overdue')

                            <div class="loan-request-notice-details">

                                @if($installmentNumber !== null)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        قسط
                                    </span>

                                        <strong>
                                            شماره {{ $installmentNumber }}
                                        </strong>

                                    </div>

                                @endif


                                @if($amount !== null)

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        مبلغ قسط
                                    </span>

                                        <strong>
                                            {{ number_format((int) $amount) }} ریال
                                        </strong>

                                    </div>

                                @endif


                                @if(isset($data['overdue_days']))

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        میزان تأخیر
                                    </span>

                                        <strong>
                                            {{ $data['overdue_days'] }} روز
                                        </strong>

                                    </div>

                                @endif


                                @if(!empty($data['due_date']))

                                    <div class="loan-request-detail">

                                    <span class="loan-request-detail-label">
                                        تاریخ سررسید
                                    </span>

                                        <strong>
                                            {{ $data['due_date'] }}
                                        </strong>

                                    </div>

                                @endif

                            </div>

                        @endif


                        {{-- =================================================
                             تاریخ اعلان
                        ================================================== --}}

                        <div class="text-muted small mt-3">

                            <i class="bi bi-calendar-event"></i>

                            {{ jdate($notification->created_at)->format('Y/m/d H:i') }}

                        </div>

                    </div>

                </div>

            @endforeach

        @else

            {{-- =====================================================
                 بدون اعلان
            ====================================================== --}}

            <div class="text-center py-5">

                <i class="bi bi-bell-slash fs-1 text-muted"></i>

                <div class="mt-3 text-muted">
                    اعلانی برای نمایش وجود ندارد.
                </div>

            </div>

        @endif


        {{-- =========================================================
             صفحه‌بندی
        ========================================================== --}}

        @if($notifications->hasPages())

            <div class="mt-4">

                {{ $notifications->links() }}

            </div>

        @endif

    </div>


@endsection
