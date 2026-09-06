@extends('layouts.app')

@section('title', 'وام‌های معوق')

@push('styles')
    @vite('resources/css/pages/loan-overdue.css')
@endpush

@section('content')

    <div class="container-fluid loan-overdue-page">

        {{-- =========================================================
             Header
        ========================================================== --}}

        <div class="loan-page-header">

            <div class="loan-page-header__content">

                <div class="loan-page-header__icon loan-page-header__icon--danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div>

                    <h1 class="loan-page-header__title">
                        وام‌های معوق
                    </h1>

                    <p class="loan-page-header__subtitle mb-0">
                        فهرست وام‌هایی که دارای اقساط سررسیدشده و پرداخت‌نشده هستند.
                    </p>

                </div>

            </div>

            <a
                href="{{ route('loans.index') }}"
                class="btn loan-back-btn">

                <i class="bi bi-arrow-right"></i>

                <span>
                    بازگشت به لیست وام‌ها
                </span>

            </a>

        </div>


        {{-- =========================================================
             Statistics
        ========================================================== --}}

        <div class="row g-3 loan-overdue-statistics">

            {{-- تعداد وام‌های معوق --}}
            <div class="col-12 col-md-4">

                <div class="loan-overdue-stat-card loan-overdue-stat-card--danger">

                    <div class="loan-overdue-stat-icon">
                        <i class="bi bi-file-earmark-x"></i>
                    </div>

                    <div class="loan-overdue-stat-content">

                        <span class="loan-overdue-stat-label">
                            وام‌های معوق
                        </span>

                        <strong class="loan-overdue-stat-value">
                            {{ number_format($statistics['loan_count']) }}
                        </strong>

                    </div>

                </div>

            </div>


            {{-- تعداد اقساط معوق --}}
            <div class="col-12 col-md-4">

                <div class="loan-overdue-stat-card loan-overdue-stat-card--warning">

                    <div class="loan-overdue-stat-icon">
                        <i class="bi bi-calendar-x"></i>
                    </div>

                    <div class="loan-overdue-stat-content">

                        <span class="loan-overdue-stat-label">
                            اقساط معوق
                        </span>

                        <strong class="loan-overdue-stat-value">
                            {{ number_format($statistics['installment_count']) }}
                        </strong>

                    </div>

                </div>

            </div>


            {{-- مبلغ کل معوقات --}}
            <div class="col-12 col-md-4">

                <div class="loan-overdue-stat-card loan-overdue-stat-card--amount">

                    <div class="loan-overdue-stat-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div class="loan-overdue-stat-content">

                        <span class="loan-overdue-stat-label">
                            مبلغ کل معوقات
                        </span>

                        <strong class="loan-overdue-stat-value">

                            {{ number_format($statistics['amount']) }}

                            <small>
                                ریال
                            </small>

                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             Main Card
        ========================================================== --}}

        <div class="loan-overdue-card">

            <div class="loan-overdue-card__header">

                <div class="loan-overdue-card__title-wrapper">

                    <div class="loan-overdue-card__icon">
                        <i class="bi bi-list-ul"></i>
                    </div>

                    <div>

                        <h2 class="loan-overdue-card__title">
                            فهرست وام‌های معوق
                        </h2>

                        <p class="loan-overdue-card__subtitle mb-0">
                            جزئیات اقساط معوق هر وام
                        </p>

                    </div>

                </div>

            </div>
            {{-- =========================================================
                 Search & Filters
            ========================================================== --}}

            <div class="loan-overdue-filters">

                <form
                    method="GET"
                    action="{{ route('loans.overdue') }}"
                    class="loan-overdue-filters__form"
                >

                    {{-- جستجو --}}
                    <div class="loan-overdue-filter-search">

                        <label for="search">
                            جستجو
                        </label>

                        <div class="loan-overdue-search-box">

                            <i class="bi bi-search"></i>

                            <input
                                type="text"
                                name="search"
                                id="search"
                                class="form-control"
                                value="{{ request('search') }}"
                                placeholder="شماره وام یا نام عضو..."
                                autocomplete="off"
                            >

                        </div>

                    </div>


                    {{-- نوع وام --}}
                    <div class="loan-overdue-filter-item">

                        <label for="loan_type">
                            نوع وام
                        </label>

                        <select
                            name="loan_type"
                            id="loan_type"
                            class="form-select"
                        >

                            <option value="">
                                همه انواع وام
                            </option>

                            @foreach($loanTypes as $loanType)

                                <option
                                    value="{{ $loanType->id }}"
                                    @selected(request('loan_type') == $loanType->id)
                                >
                                {{ $loanType->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- میزان تأخیر --}}
                    <div class="loan-overdue-filter-item">

                        <label for="delay">
                            میزان تأخیر
                        </label>

                        <select
                            name="delay"
                            id="delay"
                            class="form-select"
                        >

                            <option value="">
                                همه
                            </option>

                            <option
                                value="less_30"
                                @selected(request('delay') === 'less_30')
                            >
                            کمتر از ۳۰ روز
                            </option>

                            <option
                                value="30_90"
                                @selected(request('delay') === '30_90')
                            >
                            ۳۰ تا ۹۰ روز
                            </option>

                            <option
                                value="more_90"
                                @selected(request('delay') === 'more_90')
                            >
                            بیشتر از ۹۰ روز
                            </option>

                        </select>

                    </div>


                    {{-- عملیات --}}
                    <div class="loan-overdue-filter-actions">

                        <button
                            type="submit"
                            class="loan-overdue-filter-btn loan-overdue-filter-btn--primary"
                        >

                            <i class="bi bi-search"></i>

                            جستجو

                        </button>


                        <a
                            href="{{ route('loans.overdue') }}"
                            class="loan-overdue-filter-btn loan-overdue-filter-btn--reset"
                        >

                            <i class="bi bi-arrow-counterclockwise"></i>

                            پاک کردن

                        </a>

                    </div>

                </form>

            </div>

            <div class="loan-overdue-card__body">

                <div class="table-responsive">

                    <table class="table loan-overdue-table align-middle mb-0">

                        <thead>

                        <tr>

                            <th>
                                شماره وام
                            </th>

                            <th>
                                عضو
                            </th>

                            <th>
                                نوع وام
                            </th>

                            <th class="text-center">
                                اقساط معوق
                            </th>

                            <th class="text-center">
                                مبلغ معوق
                            </th>

                            <th class="text-center">
                                قدیمی‌ترین سررسید
                            </th>

                            <th class="text-center">
                                بیشترین تأخیر
                            </th>

                            <th class="text-center">
                                عملیات
                            </th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($loans as $loan)

                            @php

                                $amount = $loan->installments->sum('amount');

                                $oldest = $loan->installments->first();

                                $days = $oldest?->overdue_days ?? 0;

                                $amountClass = match (true) {

                                    $amount >= 40000000 => 'danger',

                                    $amount >= 10000000 => 'warning',

                                    default => 'success',

                                };

                                $delayClass = match (true) {

                                    $days >= 90 => 'danger',

                                    $days >= 30 => 'warning',

                                    default => 'secondary',

                                };

                            @endphp


                            <tr>

                                {{-- شماره وام --}}
                                <td>

                                    <span class="loan-overdue-number">

                                        {{ $loan->loanType->prefix }}

                                        <span>-</span>

                                        {{ $loan->loan_number }}

                                    </span>

                                </td>


                                {{-- عضو --}}
                                <td>

                                    <div class="loan-overdue-customer">

                                        <div class="loan-overdue-customer__icon">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <span>
                                            {{ $loan->customer->full_name }}
                                        </span>

                                    </div>

                                </td>


                                {{-- نوع وام --}}
                                <td>

                                    <span class="loan-overdue-loan-type">
                                        {{ $loan->loanType->name }}
                                    </span>

                                </td>


                                {{-- اقساط معوق --}}
                                <td class="text-center">

                                    <span class="loan-overdue-badge loan-overdue-badge--danger">

                                        <i class="bi bi-exclamation-circle"></i>

                                        {{ number_format($loan->overdue_count) }}

                                    </span>

                                </td>


                                {{-- مبلغ معوق --}}
                                <td class="text-center">

                                    <span
                                        class="loan-overdue-amount loan-overdue-amount--{{ $amountClass }}">

                                        {{ number_format($amount) }}

                                        <small>
                                            ریال
                                        </small>

                                    </span>

                                </td>


                                {{-- قدیمی‌ترین سررسید --}}
                                <td class="text-center">

                                    <span class="loan-overdue-date">

                                        {{ $oldest?->due_date_jalali ?? '-' }}

                                    </span>

                                </td>


                                {{-- بیشترین تأخیر --}}
                                <td class="text-center">

                                    <span
                                        class="loan-overdue-delay loan-overdue-delay--{{ $delayClass }}">

                                        {{ number_format($days) }}

                                        <small>
                                            روز
                                        </small>

                                    </span>

                                </td>


                                {{-- عملیات --}}
                                <td class="text-center">

                                    <a
                                        href="{{ route('loans.show', $loan) }}"
                                        class="loan-overdue-action">

                                        <span class="loan-overdue-action__icon">
                                            <i class="bi bi-eye"></i>
                                        </span>

                                        <span>
                                            مشاهده
                                        </span>

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="loan-overdue-empty">

                                    <div class="loan-overdue-empty__icon">
                                        <i class="bi bi-check-circle"></i>
                                    </div>

                                    <strong>
                                        وام معوقی وجود ندارد
                                    </strong>

                                    <span>
                                        در حال حاضر هیچ قسط سررسیدشده و پرداخت‌نشده‌ای ثبت نشده است.
                                    </span>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

@endsection
