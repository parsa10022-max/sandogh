@extends('customer.layouts.app')

@section('title', 'جزئیات وام')

@section('content')

    <div class="customer-loan-show-page" dir="rtl">

        {{-- =====================================================
             HEADER
             ===================================================== --}}
        <div class="customer-loan-show-header">

            <div class="customer-loan-show-header-main">

                <a
                    href="{{ route('customer.loans.index') }}"
                    class="customer-loan-back"
                >
                    <i class="bi bi-arrow-right"></i>
                </a>

                <div class="customer-loan-show-header-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div class="customer-loan-show-title">

                    <h1>جزئیات وام</h1>

                    <span>
                    {{ $loan->full_loan_number }}
                </span>

                </div>

            </div>


            {{-- وضعیت وام --}}
            @if($loan->status?->value === 'active')

                <span class="customer-loan-show-status active">
                <i class="bi bi-check-circle-fill"></i>
                فعال
            </span>

            @elseif($loan->status?->value === 'finished')

                <span class="customer-loan-show-status finished">
                <i class="bi bi-check-circle-fill"></i>
                تسویه شده
            </span>

            @else

                <span class="customer-loan-show-status cancelled">
                <i class="bi bi-x-circle-fill"></i>
                لغو شده
            </span>

            @endif

        </div>


        {{-- =====================================================
             SUMMARY
             ===================================================== --}}
        <section class="customer-loan-show-summary">

            <div class="customer-loan-summary-card amount">

                <div class="customer-loan-summary-card-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div>
                    <span>مبلغ وام</span>

                    <strong>
                        {{ number_format($loan->loan_amount) }}
                        <small>ریال</small>
                    </strong>
                </div>

            </div>


            <div class="customer-loan-summary-card installment">

                <div class="customer-loan-summary-card-icon">
                    <i class="bi bi-calendar2-check"></i>
                </div>

                <div>
                    <span>مبلغ هر قسط</span>

                    <strong>
                        {{ number_format($loan->installment_amount) }}
                        <small>ریال</small>
                    </strong>
                </div>

            </div>


            <div class="customer-loan-summary-card remaining">

                <div class="customer-loan-summary-card-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <div>
                    <span>باقی‌مانده</span>

                    <strong>
                        {{ number_format($loan->remainingAmount()) }}
                        <small>ریال</small>
                    </strong>
                </div>

            </div>


            <div class="customer-loan-summary-card count">

                <div class="customer-loan-summary-card-icon">
                    <i class="bi bi-list-ol"></i>
                </div>

                <div>
                    <span>اقساط باقی‌مانده</span>

                    <strong>
                        {{ $loan->remainingInstallmentsCount() }}
                        <small>قسط</small>
                    </strong>
                </div>

            </div>

        </section>


        {{-- =====================================================
             REPAYMENT PROGRESS
             ===================================================== --}}
        @php
            $paidCount = $loan->paidInstallmentsCount();

            $totalCount = (int) $loan->installment_count;

            $paidAmount = $loan->paidAmount();

            $remainingAmount = $loan->remainingAmount();

            $progress = $loan->loan_amount > 0
                ? min(
                    100,
                    round(
                        ($paidAmount / $loan->loan_amount) * 100
                    )
                )
                : 0;
        @endphp


        <section class="customer-loan-progress-card">

            <div class="customer-loan-progress-card-header">

                <div>
                    <h2>
                        وضعیت بازپرداخت
                    </h2>

                    <span>
                    پیشرفت پرداخت اقساط وام
                </span>
                </div>

                <strong>
                    {{ $progress }}٪
                </strong>

            </div>


            <div class="customer-loan-progress-track">

                <div
                    class="customer-loan-progress-fill"
                    style="width: {{ $progress }}%"
                ></div>

            </div>


            <div class="customer-loan-progress-stats">

                <div>
                    <span>پرداخت شده</span>

                    <strong>
                        {{ $paidCount }}
                        <small>از {{ $totalCount }} قسط</small>
                    </strong>
                </div>


                <div>
                    <span>مبلغ پرداخت شده</span>

                    <strong>
                        {{ number_format($paidAmount) }}
                        <small>ریال</small>
                    </strong>
                </div>


                <div>
                    <span>مبلغ باقی‌مانده</span>

                    <strong>
                        {{ number_format($remainingAmount) }}
                        <small>ریال</small>
                    </strong>
                </div>

            </div>

        </section>


        {{-- =====================================================
             LOAN INFORMATION
             ===================================================== --}}
        <section class="customer-loan-info-card">

            <div class="customer-loan-section-title">

                <div class="customer-loan-section-title-icon">
                    <i class="bi bi-info-circle"></i>
                </div>

                <div>
                    <h2>اطلاعات وام</h2>

                    <span>
                    مشخصات اصلی وام
                </span>
                </div>

            </div>


            <div class="customer-loan-info-grid">

                <div class="customer-loan-info-item">

                    <span>شماره وام</span>

                    <strong>
                        {{ $loan->full_loan_number }}
                    </strong>

                </div>


                <div class="customer-loan-info-item">

                    <span>نوع وام</span>

                    <strong>
                        {{ $loan->loanType?->name ?? '—' }}
                    </strong>

                </div>


                <div class="customer-loan-info-item">

                    <span>تعداد اقساط</span>

                    <strong>
                        {{ $loan->installment_count }}
                        قسط
                    </strong>

                </div>


                <div class="customer-loan-info-item">

                    <span>فاصله اقساط</span>

                    <strong>
                        {{ $loan->installment_interval?->label() ?? '—' }}
                    </strong>

                </div>


                <div class="customer-loan-info-item">

                    <span>تاریخ شروع</span>

                    <strong>
                        {{ $loan->start_date_jalali }}
                    </strong>

                </div>


                <div class="customer-loan-info-item">

                    <span>اولین سررسید</span>

                    <strong>
                        {{ $loan->first_due_date_jalali }}
                    </strong>

                </div>


                <div class="customer-loan-info-item">

                    <span>آخرین سررسید</span>

                    <strong>
                        {{ $loan->last_due_date_jalali }}
                    </strong>

                </div>

            </div>

        </section>


        {{-- =====================================================
             INSTALLMENTS
             ===================================================== --}}





        <section class="customer-loan-installments-card">

            {{-- =====================================================
                 TITLE
                 ===================================================== --}}
            <div class="customer-loan-section-title">

                <div class="customer-loan-section-title-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>

                <div>
                    <h2>اقساط وام</h2>

                    <span>
                وضعیت و پرداخت اقساط
            </span>
                </div>

            </div>


            {{-- =====================================================
                 INSTALLMENTS
                 ===================================================== --}}
            @if($loan->installments->count())

                @php

                    /*
                    |--------------------------------------------------------------------------
                    | پیدا کردن اولین قسط پرداخت نشده
                    |--------------------------------------------------------------------------
                    |
                    | فقط همین قسط اجازه پرداخت دارد.
                    |
                    */

                    $nextPayableInstallment = $loan->installments
                        ->sortBy('installment_number')
                        ->first(function ($item) {

                            return
                                is_null($item->paid_at)
                                && $item->status !== \App\Enums\InstallmentStatus::PAID;

                        });

                @endphp


                <div class="customer-loan-installments-list">

                    @foreach($loan->installments->sortBy('installment_number') as $installment)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | وضعیت قسط
                            |--------------------------------------------------------------------------
                            */

                            $isPaid =
                                !is_null($installment->paid_at)
                                || $installment->status === \App\Enums\InstallmentStatus::PAID;


                            $isOverdue =
                                !$isPaid
                                && $installment->status === \App\Enums\InstallmentStatus::OVERDUE;


                            /*
                            |--------------------------------------------------------------------------
                            | آیا این همان اولین قسط قابل پرداخت است؟
                            |--------------------------------------------------------------------------
                            */

                            $isNextPayable =
                                $nextPayableInstallment
                                && $installment->id === $nextPayableInstallment->id;

                        @endphp


                        <div class="customer-installment-item">

                            {{-- =================================================
                                 شماره قسط
                                 ================================================= --}}
                            <div class="customer-installment-number">

                        <span>
                            قسط
                        </span>

                                <strong>
                                    {{ $installment->installment_number }}
                                </strong>

                            </div>


                            {{-- =================================================
                                 تاریخ سررسید
                                 ================================================= --}}
                            <div class="customer-installment-date">

                        <span>
                            سررسید
                        </span>

                                <strong>
                                    {{ $installment->due_date_jalali ?? '—' }}
                                </strong>

                            </div>


                            {{-- =================================================
                                 مبلغ
                                 ================================================= --}}
                            <div class="customer-installment-amount">

                        <span>
                            مبلغ
                        </span>

                                <strong>
                                    {{ number_format($installment->amount) }}

                                    <small>
                                        ریال
                                    </small>
                                </strong>

                            </div>


                            {{-- =================================================
                                 وضعیت و عملیات
                                 ================================================= --}}
                            <div class="customer-installment-status">


                                {{-- =================================================
                                     پرداخت شده
                                     ================================================= --}}
                                @if($isPaid)

                                    <span class="paid">

                                <i class="bi bi-check-circle-fill"></i>

                                پرداخت شده

                            </span>


                                    {{-- =================================================
                                         اولین قسط قابل پرداخت
                                         ================================================= --}}
                                @elseif($isNextPayable)

                                    <div class="customer-installment-action">

                                        @if($isOverdue)

                                            <span class="overdue">

                                        <i class="bi bi-exclamation-circle-fill"></i>

                                        معوق

                                    </span>

                                        @else

                                            <span class="pending">

                                        <i class="bi bi-clock-fill"></i>

                                        پرداخت نشده

                                    </span>

                                        @endif


                                        <form
                                            method="POST"
                                            action="{{ route('payments.pay', $installment) }}"
                                            class="customer-installment-pay-form"
                                        >

                                            @csrf

                                            <button
                                                type="submit"
                                                class="customer-installment-pay-btn"
                                            >

                                                <i class="bi bi-credit-card"></i>

                                                پرداخت قسط

                                            </button>

                                        </form>

                                    </div>


                                    {{-- =================================================
                                         اقساط بعدی
                                         ================================================= --}}
                                @else

                                    <span class="pending">

                                <i class="bi bi-lock-fill"></i>

                                در انتظار پرداخت قسط قبلی

                            </span>

                                @endif

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- =====================================================
                     EMPTY
                     ===================================================== --}}
            @else

                <div class="customer-loan-installments-empty">

                    <i class="bi bi-calendar-x"></i>

                    <span>
                هنوز قسطی برای این وام ثبت نشده است.
            </span>

                </div>

            @endif

        </section>












        {{-- =====================================================
             BACK BUTTON
             ===================================================== --}}
        <div class="customer-loan-show-footer">

            <a
                href="{{ route('customer.loans.index') }}"
                class="customer-loan-back-button"
            >
                <i class="bi bi-arrow-right"></i>
                بازگشت به وام‌های من
            </a>

        </div>

    </div>

@endsection
