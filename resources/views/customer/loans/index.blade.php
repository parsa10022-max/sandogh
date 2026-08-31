@extends('customer.layouts.app')

@section('title', 'وام‌های من')

@section('content')


    <div class="customer-loans-page">

        {{-- =====================================================
             PAGE HEADER
             ===================================================== --}}
        <section class="customer-loans-header">

            <div class="customer-loans-header-main">

                <div class="customer-loans-header-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div class="customer-loans-header-content">

                    <h1 class="customer-loans-title">
                        وام‌های من
                    </h1>

                    <p class="customer-loans-subtitle">
                        مشاهده و مدیریت وضعیت وام‌ها و اقساط
                    </p>

                </div>

            </div>

            <a
                href="{{ route('customer.dashboard') }}"
                class="customer-loans-back"
            >
                <i class="bi bi-arrow-right"></i>

                <span>
                داشبورد
            </span>
            </a>

        </section>


        {{-- =====================================================
             SUMMARY
             ===================================================== --}}
        @php

            $totalLoans = $loans->total();

            $activeLoans = $loans->getCollection()
                ->filter(fn ($loan) =>
                    $loan->status?->value === \App\Enums\LoanStatus::ACTIVE->value
                )
                ->count();

            $finishedLoans = $loans->getCollection()
                ->filter(fn ($loan) =>
                    $loan->status?->value === \App\Enums\LoanStatus::FINISHED->value
                )
                ->count();

        @endphp


        <section class="customer-loans-summary">

            {{-- مجموع --}}
            <div class="customer-loans-summary-card">

                <div class="customer-loans-summary-icon total">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div class="customer-loans-summary-content">

                <span class="customer-loans-summary-label">
                    مجموع وام‌ها
                </span>

                    <strong class="customer-loans-summary-value">
                        {{ number_format($totalLoans) }}
                    </strong>

                </div>

            </div>


            {{-- فعال --}}
            <div class="customer-loans-summary-card">

                <div class="customer-loans-summary-icon active">
                    <i class="bi bi-activity"></i>
                </div>

                <div class="customer-loans-summary-content">

                <span class="customer-loans-summary-label">
                    وام فعال
                </span>

                    <strong class="customer-loans-summary-value success">
                        {{ number_format($activeLoans) }}
                    </strong>

                </div>

            </div>


            {{-- تسویه --}}
            <div class="customer-loans-summary-card">

                <div class="customer-loans-summary-icon finished">
                    <i class="bi bi-check2-all"></i>
                </div>

                <div class="customer-loans-summary-content">

                <span class="customer-loans-summary-label">
                    تسویه‌شده
                </span>

                    <strong class="customer-loans-summary-value">
                        {{ number_format($finishedLoans) }}
                    </strong>

                </div>

            </div>


            {{-- تعداد نمایش داده شده --}}
            <div class="customer-loans-summary-card">

                <div class="customer-loans-summary-icon remaining">
                    <i class="bi bi-list-check"></i>
                </div>

                <div class="customer-loans-summary-content">

                <span class="customer-loans-summary-label">
                    در این صفحه
                </span>

                    <strong class="customer-loans-summary-value">
                        {{ number_format($loans->count()) }}
                    </strong>

                </div>

            </div>

        </section>


        {{-- =====================================================
             LOANS SECTION
             ===================================================== --}}
        <section class="customer-loans-list-section">

            {{-- عنوان لیست --}}
            <div class="customer-loans-list-header">

                <div class="customer-loans-list-title">

                    <div class="customer-loans-list-title-icon">
                        <i class="bi bi-credit-card-2-front"></i>
                    </div>

                    <div>

                        <h2>
                            فهرست وام‌ها
                        </h2>

                        <span>
                        جزئیات و روند بازپرداخت
                    </span>

                    </div>

                </div>

                <div class="customer-loans-count">
                    <strong>
                        {{ number_format($loans->total()) }}
                    </strong>

                    <span>
                    وام
                </span>
                </div>

            </div>


            {{-- =================================================
                 EMPTY STATE
                 ================================================= --}}
            @if($loans->isEmpty())

                <div class="customer-loans-empty">

                    <div class="customer-loans-empty-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <h3 class="customer-loans-empty-title">
                        هنوز وامی ندارید
                    </h3>

                    <p class="customer-loans-empty-text">
                        در حال حاضر هیچ وامی برای حساب شما ثبت نشده است.
                    </p>

                </div>

            @else

                {{-- =================================================
                     LOANS
                     ================================================= --}}
                <div class="customer-loans-grid">

                    @foreach($loans as $loan)

                        @php

                            $paidCount = $loan->paidInstallmentsCount();

                            $remainingCount = $loan->remainingInstallmentsCount();

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

                            $isActive =
                                $loan->status?->value ===
                                \App\Enums\LoanStatus::ACTIVE->value;

                            $isFinished =
                                $loan->status?->value ===
                                \App\Enums\LoanStatus::FINISHED->value;

                        @endphp


                        {{-- =================================================
                             LOAN CARD
                             ================================================= --}}
                        <article class="customer-loan-card">


                            {{-- HEADER --}}
                            <div class="customer-loan-card-header">

                                <div class="customer-loan-card-title">

                                    <div class="customer-loan-card-icon">
                                        <i class="bi bi-cash-coin"></i>
                                    </div>

                                    <div class="customer-loan-card-name">

                                    <span class="customer-loan-card-label">
                                        شماره وام
                                    </span>

                                        <strong>
                                            {{ $loan->full_loan_number }}
                                        </strong>

                                    </div>

                                </div>


                                @if($isActive)

                                    <span class="customer-loan-status active">
                                    <i class="bi bi-circle-fill"></i>
                                    فعال
                                </span>

                                @elseif($isFinished)

                                    <span class="customer-loan-status finished">
                                    <i class="bi bi-check-circle-fill"></i>
                                    تسویه‌شده
                                </span>

                                @else

                                    <span class="customer-loan-status cancelled">
                                    <i class="bi bi-x-circle-fill"></i>
                                    لغوشده
                                </span>

                                @endif

                            </div>


                            {{-- LOAN TYPE --}}
                            <div class="customer-loan-type">

                                <i class="bi bi-tag-fill"></i>

                                <span>
                                {{ $loan->loanType?->name ?? 'وام' }}
                            </span>

                            </div>


                            {{-- MAIN AMOUNT --}}
                            <div class="customer-loan-main">

                                <div class="customer-loan-main-label">
                                    مبلغ وام
                                </div>

                                <div class="customer-loan-main-value">

                                    <strong>
                                        {{ number_format($loan->loan_amount) }}
                                    </strong>

                                    <small>
                                        ریال
                                    </small>

                                </div>

                            </div>


                            {{-- DATA --}}
                            <div class="customer-loan-data-grid">

                                <div class="customer-loan-data">

                                <span>
                                    مبلغ قسط
                                </span>

                                    <strong>
                                        {{ number_format($loan->installment_amount) }}

                                        <small>
                                            ریال
                                        </small>
                                    </strong>

                                </div>


                                <div class="customer-loan-data">

                                <span>
                                    تعداد اقساط
                                </span>

                                    <strong>
                                        {{ number_format($loan->installment_count) }}

                                        <small>
                                            قسط
                                        </small>
                                    </strong>

                                </div>


                                <div class="customer-loan-data success">

                                <span>
                                    پرداخت‌شده
                                </span>

                                    <strong>
                                        {{ number_format($paidCount) }}

                                        <small>
                                            قسط
                                        </small>
                                    </strong>

                                </div>


                                <div class="customer-loan-data danger">

                                <span>
                                    باقی‌مانده
                                </span>

                                    <strong>
                                        {{ number_format($remainingAmount) }}

                                        <small>
                                            ریال
                                        </small>
                                    </strong>

                                </div>

                            </div>


                            {{-- PROGRESS --}}
                            <div class="customer-loan-progress-section">

                                <div class="customer-loan-progress-header">

                                <span>
                                    پیشرفت بازپرداخت
                                </span>

                                    <strong>
                                        {{ $progress }}٪
                                    </strong>

                                </div>

                                <div class="customer-loan-progress-track">

                                    <div
                                        class="customer-loan-progress-bar"
                                        style="width: {{ $progress }}%;"
                                    ></div>

                                </div>

                                <div class="customer-loan-progress-footer">

                                <span>
                                    {{ number_format($paidCount) }} قسط پرداخت شده
                                </span>

                                    <span>
                                    {{ number_format($remainingCount) }} قسط باقی‌مانده
                                </span>

                                </div>

                            </div>


                            {{-- FOOTER --}}
                            <div class="customer-loan-card-footer">

                                <div class="customer-loan-start-date">

                                    <div class="customer-loan-start-icon">
                                        <i class="bi bi-calendar3"></i>
                                    </div>

                                    <div>

                                    <span>
                                        شروع وام
                                    </span>

                                        <strong>
                                            {{ $loan->start_date_jalali }}
                                        </strong>

                                    </div>

                                </div>


                                <a
                                    href="{{ route('customer.loans.show', $loan) }}"
                                    class="customer-loan-details-button"
                                >

                                <span>
                                    جزئیات
                                </span>

                                    <i class="bi bi-arrow-left"></i>

                                </a>

                            </div>

                        </article>

                    @endforeach

                </div>


                {{-- PAGINATION --}}
                @if($loans->hasPages())

                    <div class="customer-loans-pagination">

                        {{ $loans->links() }}

                    </div>

                @endif

            @endif

        </section>

    </div>


@endsection
