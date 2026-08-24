@extends('customer.layouts.app')

@section('title', 'درخواست‌های وام')

@section('header_title', 'درخواست‌های وام')

@section('header_subtitle', 'پیگیری درخواست‌های وام شما')

@section('content')

    <div class="customer-dashboard">

        {{-- =========================================================
             Header
        ========================================================== --}}

        <div class="customer-page-header">

            <div>

                <h2>
                    درخواست‌های وام من
                </h2>

                <span>
                    مشاهده و پیگیری وضعیت درخواست‌های وام
                </span>

            </div>


            @if($canCreateNewRequest)
                <a href="{{ route('customer.loan-request.create') }}"
                   class="btn btn-primary">

                    <i class="bi bi-plus-circle"></i>

                    درخواست وام جدید

                </a>
            @endif

        </div>


        {{-- =========================================================
             Success Message
        ========================================================== --}}

        @if(session('success'))

            <div class="alert alert-success d-flex align-items-center gap-2 mb-4">

                <i class="bi bi-check-circle-fill"></i>

                <span>
                    {{ session('success') }}
                </span>

            </div>

        @endif


        {{-- =========================================================
             Error Message
        ========================================================== --}}

        @if(session('error'))

            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">

                <i class="bi bi-exclamation-circle-fill"></i>

                <span>
                    {{ session('error') }}
                </span>

            </div>

        @endif


        {{-- =========================================================
             Loan Requests
        ========================================================== --}}

        <section class="customer-loan-requests-section">

            @forelse($loanRequests as $loanRequest)

                @php

                    $status = $loanRequest->status instanceof \BackedEnum
                        ? $loanRequest->status->value
                        : $loanRequest->status;

                @endphp


                <div class="customer-loan-request-card">


                    {{-- =================================================
                         Header
                    ================================================== --}}

                    <div class="customer-loan-request-header">

                        <div class="customer-loan-request-title">

                            <span class="customer-loan-request-icon">

                                <i class="bi bi-cash-coin"></i>

                            </span>

                            <div>

                                <h3>
                                    درخواست وام
                                </h3>

                                <span>
                                    شماره درخواست:
                                    {{ $loanRequest->id }}
                                </span>

                            </div>

                        </div>


                        {{-- وضعیت درخواست --}}

                        @if($status === 'pending')

                            <span class="customer-loan-request-status pending">

                                <i class="bi bi-clock-fill"></i>

                                در حال بررسی

                            </span>

                        @elseif($status === 'approved')

                            <span class="customer-loan-request-status approved">

                                <i class="bi bi-check-circle-fill"></i>

                                تأیید شده

                            </span>

                        @elseif($status === 'rejected')

                            <span class="customer-loan-request-status rejected">

                                <i class="bi bi-x-circle-fill"></i>

                                رد شده

                            </span>

                        @else

                            <span class="customer-loan-request-status">

                                {{ $status }}

                            </span>

                        @endif

                    </div>


                    {{-- =================================================
                         اطلاعات درخواست
                    ================================================== --}}

                    <div class="customer-loan-request-body">


                        {{-- مبلغ درخواستی --}}

                        <div class="customer-loan-request-info">

                            <span class="customer-loan-request-label">
                                مبلغ درخواستی
                            </span>

                            <strong class="customer-loan-request-amount">

                                {{ number_format($loanRequest->requested_amount) }}

                                <small>
                                    ریال
                                </small>

                            </strong>

                        </div>


                        {{-- تاریخ ثبت --}}

                        <div class="customer-loan-request-info">

                            <span class="customer-loan-request-label">
                                تاریخ ثبت
                            </span>

                            <strong class="customer-loan-request-value">

                                {{ \Morilog\Jalali\Jalalian::fromCarbon(
                                    \Carbon\Carbon::parse($loanRequest->created_at)
                                )->format('Y/m/d') }}

                            </strong>

                        </div>


                        {{-- تاریخ بررسی --}}

                        @if($loanRequest->reviewed_at)

                            <div class="customer-loan-request-info">

                                <span class="customer-loan-request-label">
                                    تاریخ بررسی
                                </span>

                                <strong class="customer-loan-request-value">

                                    {{ \Morilog\Jalali\Jalalian::fromCarbon(
                                        \Carbon\Carbon::parse($loanRequest->reviewed_at)
                                    )->format('Y/m/d') }}

                                </strong>

                            </div>

                        @endif


                        {{-- =================================================
                             اطلاعات تأیید
                        ================================================== --}}

                        @if($status === 'approved')


                            {{-- مبلغ تأیید شده --}}

                            @if($loanRequest->approved_amount)

                                <div class="customer-loan-request-info">

                                    <span class="customer-loan-request-label">
                                        مبلغ تأییدشده
                                    </span>

                                    <strong class="customer-loan-request-amount">

                                        {{ number_format($loanRequest->approved_amount) }}

                                        <small>
                                            ریال
                                        </small>

                                    </strong>

                                </div>

                            @endif


                            {{-- تعداد اقساط --}}

                            @if($loanRequest->approved_installment_count)

                                <div class="customer-loan-request-info">

                                    <span class="customer-loan-request-label">
                                        تعداد اقساط
                                    </span>

                                    <strong class="customer-loan-request-value">

                                        {{ $loanRequest->approved_installment_count }}

                                        قسط

                                    </strong>

                                </div>

                            @endif


                            {{-- فاصله اقساط --}}

                            @if($loanRequest->approved_installment_interval)

                                <div class="customer-loan-request-info">

                                    <span class="customer-loan-request-label">
                                        فاصله اقساط
                                    </span>

                                    <strong class="customer-loan-request-value">

                                        @if($loanRequest->approved_installment_interval == 1)

                                            ماهانه

                                        @elseif($loanRequest->approved_installment_interval == 3)

                                            سه‌ماهه

                                        @else

                                            هر
                                            {{ $loanRequest->approved_installment_interval }}
                                            ماه

                                        @endif

                                    </strong>

                                </div>

                            @endif

                        @endif

                    </div>


                    {{-- =================================================
                         توضیح بررسی
                    ================================================== --}}

                    @if($loanRequest->review_note)

                        <div class="customer-loan-request-review">

                            <div class="customer-loan-request-review-icon">

                                <i class="bi bi-chat-left-text-fill"></i>

                            </div>


                            <div class="customer-loan-request-review-content">

                                <div class="customer-loan-request-review-title">

                                    توضیح بررسی

                                </div>


                                <p class="customer-loan-request-review-text">

                                    {{ $loanRequest->review_note }}

                                </p>


                                {{-- تاریخ مراجعه مجدد --}}

                                @if($loanRequest->next_review_date)

                                    <div class="customer-loan-request-next-review">

                                        <i class="bi bi-calendar-event-fill"></i>

                                        <span>
                                            تاریخ مراجعه مجدد:
                                        </span>

                                        <strong>

                                            {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                                $loanRequest->next_review_date
                                            )->format('Y/m/d') }}

                                        </strong>

                                    </div>

                                @endif

                            </div>

                        </div>

                    @endif


                    {{-- =================================================
                         وام ایجاد شده
                    ================================================== --}}

                    @if($status === 'approved' && $loanRequest->loan)

                        <div class="customer-loan-request-loan-created">

                            <div>

                                <i class="bi bi-check-circle-fill"></i>

                                <span>
                                    وام شما ایجاد شده است.
                                </span>

                            </div>

                            <div class="customer-loan-request-loan-number">

    <span>
        شماره وام:
    </span>

                                <strong dir="ltr">
                                    {{ $loanRequest->loan->full_loan_number }}
                                </strong>

                            </div>

                        </div>

                    @endif


                    {{-- =================================================
                         Footer
                    ================================================== --}}

                    <div class="customer-loan-request-footer">

                        <a href="{{ route(
                            'customer.loan-request.show',
                            $loanRequest
                        ) }}"
                           class="customer-loan-request-details">

                            مشاهده جزئیات

                            <i class="bi bi-arrow-left"></i>

                        </a>

                    </div>

                </div>


            @empty


                {{-- =====================================================
                     Empty State
                ====================================================== --}}

                <div class="customer-empty-state">

                    <div class="customer-empty-state-icon">

                        <i class="bi bi-file-earmark-text"></i>

                    </div>


                    <h3>
                        هنوز درخواست وامی ثبت نکرده‌اید
                    </h3>


                    <p>
                        برای ثبت درخواست وام، روی دکمه زیر کلیک کنید.
                    </p>


                    <a href="{{ route('customer.loan-request.create') }}"
                       class="btn btn-primary">

                        <i class="bi bi-plus-circle"></i>

                        ثبت درخواست وام

                    </a>

                </div>

            @endforelse


            {{-- =========================================================
                 Pagination
            ========================================================== --}}

            @if($loanRequests->hasPages())

                <div class="mt-4">

                    {{ $loanRequests->links() }}

                </div>

            @endif

        </section>

    </div>

@endsection
