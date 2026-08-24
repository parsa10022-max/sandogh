
@extends('customer.layouts.app')

@section('title', 'جزئیات درخواست وام')

@section('header_title', 'جزئیات درخواست وام')

@section('header_subtitle', 'مشاهده وضعیت و اطلاعات درخواست')

@push('styles')
    <link rel="stylesheet"
          href="{{ asset('css/customer/loan-request-show.css') }}">
@endpush

@section('content')

    <div class="customer-loan-request-show-page">

        {{-- =====================================================
             Page Header
        ====================================================== --}}

        <div class="loan-request-show-top">

            <a href="{{ route('customer.loan-requests.index') }}"
               class="loan-request-back-button">

                <i class="bi bi-arrow-right"></i>

                بازگشت به درخواست‌ها

            </a>

        </div>


        {{-- =====================================================
             Main Card
        ====================================================== --}}

        <div class="loan-request-show-card">

            {{-- Header --}}

            <div class="loan-request-show-header">

                <div class="loan-request-show-title">

                    <div class="loan-request-show-icon">

                        <i class="bi bi-cash-coin"></i>

                    </div>

                    <div>

                        <h2>
                            درخواست وام
                        </h2>

                        <span>
                            شماره درخواست:
                            {{ $loanRequest->id }}
                        </span>

                    </div>

                </div>


                {{-- Status --}}

                @php

                    $status = $loanRequest->status instanceof \BackedEnum
                        ? $loanRequest->status->value
                        : $loanRequest->status;

                @endphp


                @if($status === 'pending')

                    <span class="loan-request-show-status pending">

                        <i class="bi bi-clock-fill"></i>

                        در حال بررسی

                    </span>

                @elseif($status === 'approved')

                    <span class="loan-request-show-status approved">

                        <i class="bi bi-check-circle-fill"></i>

                        تأیید شده

                    </span>

                @elseif($status === 'rejected')

                    <span class="loan-request-show-status rejected">

                        <i class="bi bi-x-circle-fill"></i>

                        رد شده

                    </span>

                @else

                    <span class="loan-request-show-status">

                        {{ $status }}

                    </span>

                @endif

            </div>


            {{-- =================================================
                 Request Information
            ================================================== --}}

            <div class="loan-request-show-body">

                <div class="loan-request-show-info">

                    <span>
                        مبلغ درخواستی
                    </span>

                    <strong class="loan-request-show-amount">

                        {{ number_format($loanRequest->requested_amount) }}

                        <small>
                            ریال
                        </small>

                    </strong>

                </div>


                <div class="loan-request-show-info">

                    <span>
                        تاریخ ثبت درخواست
                    </span>

                    <strong>

                        {{ \Morilog\Jalali\Jalalian::fromCarbon($loanRequest->created_at)->format('Y/m/d') }}

                    </strong>

                </div>


                @if($loanRequest->reviewed_at)

                    <div class="loan-request-show-info">

                        <span>
                            تاریخ بررسی
                        </span>

                        <strong>

                            {{ \Morilog\Jalali\Jalalian::fromCarbon($loanRequest->reviewed_at)->format('Y/m/d') }}

                        </strong>

                    </div>

                @endif

            </div>


            {{-- =================================================
                 Description
            ================================================== --}}

            @if($loanRequest->description)

                <div class="loan-request-show-description">

                    <div class="loan-request-show-section-title">

                        <i class="bi bi-chat-left-text"></i>

                        توضیحات درخواست

                    </div>

                    <p>
                        {{ $loanRequest->description }}
                    </p>

                </div>

            @endif


            {{-- =================================================
                 Loan Information
            ================================================== --}}

            @if($loanRequest->loan)

                <div class="loan-request-show-loan">

                    <div class="loan-request-show-section-title">

                        <i class="bi bi-wallet2"></i>

                        اطلاعات وام

                    </div>

                    <div class="loan-request-show-loan-grid">

                        <div>

                            <span>
                                شماره وام
                            </span>

                            <strong>
                                {{ $loanRequest->loan->full_loan_number }}
                            </strong>

                        </div>

                        <div>

                            <span>
                                مبلغ وام
                            </span>

                            <strong>
                                {{ number_format($loanRequest->loan->loan_amount) }}
                                <small>ریال</small>
                            </strong>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =================================================
                 Footer
            ================================================== --}}

            <div class="loan-request-show-footer">

                <a href="{{ route('customer.loan-requests.index') }}"
                   class="loan-request-show-cancel">

                    <i class="bi bi-arrow-right"></i>

                    بازگشت

                </a>

            </div>

        </div>

    </div>

@endsection

