@extends('layouts.app')

@section('title', 'گزارش عملیات روزانه صندوق')

@section('content')

    <div class="container-fluid daily-operations-report">

        @php

            /*
            |--------------------------------------------------------------------------
            | تبدیل تاریخ میلادی به شمسی
            |--------------------------------------------------------------------------
            */

            $jalaliDate = function ($date, $withTime = false) {

                if (!$date) {
                    return '—';
                }

                return \Morilog\Jalali\Jalalian::fromCarbon($date)
                    ->format($withTime ? 'Y/m/d H:i' : 'Y/m/d');
            };


            /*
            |--------------------------------------------------------------------------
            | فرمت شماره حساب
            |--------------------------------------------------------------------------
            */

            $formatAccountNumber = function ($accountNumber) {

                if (empty($accountNumber)) {
                    return '—';
                }

                $accountNumber = str_replace(
                    '-',
                    '',
                    (string) $accountNumber
                );

                if (strlen($accountNumber) <= 4) {
                    return $accountNumber;
                }

                return substr($accountNumber, 0, 4)
                    . '-'
                    . substr($accountNumber, 4);
            };

        @endphp


        {{-- =========================================
             Header
             ========================================= --}}

        <div class="card border-0 shadow-sm rounded-4 mb-4 report-header">

            <div class="card-body">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                    <div>

                        <h4 class="fw-bold mb-1">

                            <i class="bi bi-journal-text me-1"></i>

                            گزارش عملیات صندوق

                        </h4>

                        <div class="text-muted small">

                            گزارش ثبت‌نام‌ها، دریافت‌ها، پرداخت‌ها و سایر عملیات صندوق

                        </div>

                    </div>


                    <div class="d-flex gap-2 no-print">

                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="window.print()"
                        >

                            <i class="bi bi-printer me-1"></i>

                            چاپ گزارش

                        </button>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================
             Filters
             ========================================= --}}

        <div class="card border-0 shadow-sm rounded-4 mb-4 no-print">

            <div class="card-body">

                <form
                    method="GET"
                    action="{{ route('admin.reports.daily-operations') }}"
                >

                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-md-2">

                            <label
                                for="from_date"
                                class="form-label fw-semibold"
                            >
                                از تاریخ
                            </label>

                            <input
                                type="text"
                                name="from_date"
                                id="from_date"
                                value="{{ request('from_date') }}"
                                class="form-control"
                                placeholder="۱۴۰۵/۰۶/۱۹"
                                autocomplete="off"
                                data-jdp
                            >

                        </div>


                        <div class="col-12 col-md-2">

                            <label
                                for="to_date"
                                class="form-label fw-semibold"
                            >
                                تا تاریخ
                            </label>

                            <input
                                type="text"
                                name="to_date"
                                id="to_date"
                                value="{{ request('to_date') }}"
                                class="form-control"
                                placeholder="۱۴۰۵/۰۶/۱۹"
                                autocomplete="off"
                                data-jdp
                            >

                        </div>


                        <div class="col-12 col-md-2">

                            <label
                                for="operation_type"
                                class="form-label fw-semibold"
                            >
                                نوع عملیات
                            </label>

                            <select
                                name="operation_type"
                                id="operation_type"
                                class="form-select"
                            >

                                <option
                                    value="all"
                                    @selected($operationType === 'all')
                                >
                                همه عملیات
                                </option>

                                <option
                                    value="registration"
                                    @selected($operationType === 'registration')
                                >
                                ثبت‌نام عضو
                                </option>

                                <option
                                    value="deposit"
                                    @selected($operationType === 'deposit')
                                >
                                واریز
                                </option>

                                <option
                                    value="withdrawal"
                                    @selected($operationType === 'withdrawal')
                                >
                                برداشت
                                </option>

                                <option
                                    value="loan_payment"
                                    @selected($operationType === 'loan_payment')
                                >
                                پرداخت قسط
                                </option>

                                <option
                                    value="transfer"
                                    @selected($operationType === 'transfer')
                                >
                                انتقال پس‌انداز
                                </option>

                                <option
                                    value="donation"
                                    @selected($operationType === 'donation')
                                >
                                کمک به صندوق
                                </option>

                                <option
                                    value="loan_request"
                                    @selected($operationType === 'loan_request')
                                >
                                درخواست وام
                                </option>

                            </select>

                        </div>


                        <div class="col-12 col-md-3">

                            <label
                                for="customer_id"
                                class="form-label fw-semibold"
                            >
                                عضو
                            </label>

                            <select
                                name="customer_id"
                                id="customer_id"
                                class="form-select"
                            >

                                <option value="">
                                    همه اعضا
                                </option>

                                @foreach($customersForFilter as $customer)

                                    <option
                                        value="{{ $customer->id }}"
                                        @selected(
                                        (string) $customerId ===
                                        (string) $customer->id
                                        )
                                        >

                                        {{ $customer->first_name }}
                                        {{ $customer->last_name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="col-12 col-md-2">

                            <div class="form-check mb-2">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="only_confirmed"
                                    value="1"
                                    id="only_confirmed"
                                    @checked($onlyConfirmed)
                                >

                                <label
                                    class="form-check-label"
                                    for="only_confirmed"
                                >
                                    فقط تأیید حسابداری
                                </label>

                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >

                                <i class="bi bi-funnel me-1"></i>

                                اعمال فیلتر

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- =========================================
             Summary
             ========================================= --}}

        <div class="row g-3 mb-4">


            {{-- ثبت‌نام --}}

            <div class="col-6 col-md-4 col-xl-2">

                <div class="card summary-card h-100">

                    <div class="card-body">

                        <div class="summary-label">
                            ثبت‌نام جدید
                        </div>

                        <div class="summary-value">

                            {{ number_format($counts['customers']) }}

                            نفر

                        </div>

                    </div>

                </div>

            </div>


            {{-- واریز --}}

            <div class="col-6 col-md-4 col-xl-2">

                <div class="card summary-card h-100">

                    <div class="card-body">

                        <div class="summary-label">
                            واریز
                        </div>

                        <div class="summary-value">

                            {{ number_format($totals['deposits']) }}

                            ریال

                        </div>

                    </div>

                </div>

            </div>


            {{-- برداشت --}}

            <div class="col-6 col-md-4 col-xl-2">

                <div class="card summary-card h-100">

                    <div class="card-body">

                        <div class="summary-label">
                            برداشت
                        </div>

                        <div class="summary-value">

                            {{ number_format($totals['withdrawals']) }}

                            ریال

                        </div>

                    </div>

                </div>

            </div>


            {{-- پرداخت اقساط --}}

            <div class="col-6 col-md-4 col-xl-2">

                <div class="card summary-card h-100">

                    <div class="card-body">

                        <div class="summary-label">
                            پرداخت اقساط
                        </div>

                        <div class="summary-value">

                            {{ number_format($totals['loan_payments']) }}

                            ریال

                        </div>

                    </div>

                </div>

            </div>


            {{-- پس‌انداز خود --}}

            <div class="col-6 col-md-4 col-xl-2">

                <div class="card summary-card h-100">

                    <div class="card-body">

                        <div class="summary-label">
                            واریز پس‌انداز خود
                        </div>

                        <div class="summary-value">

                            {{ number_format($totals['own_savings_transfers']) }}

                            ریال

                        </div>

                    </div>

                </div>

            </div>


            {{-- پس‌انداز دیگران --}}

            <div class="col-6 col-md-4 col-xl-2">

                <div class="card summary-card h-100">

                    <div class="card-body">

                        <div class="summary-label">
                            واریز پس‌انداز دیگران
                        </div>

                        <div class="summary-value">

                            {{ number_format($totals['other_savings_transfers']) }}

                            ریال

                        </div>

                    </div>

                </div>

            </div>


            {{-- کمک به صندوق --}}

            <div class="col-6 col-md-4 col-xl-2">

                <div class="card summary-card h-100">

                    <div class="card-body">

                        <div class="summary-label">
                            کمک به صندوق
                        </div>

                        <div class="summary-value">

                            {{ number_format($totals['donations']) }}

                            ریال

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================
             Overall Status
             ========================================= --}}

        <div class="card border-0 shadow-sm rounded-4 mb-4">

            <div class="card-body">

                <div class="row g-3 text-center">


                    <div class="col-6 col-md-3">

                        <div class="summary-label">
                            تعداد عملیات
                        </div>

                        <div class="summary-value">

                            {{ number_format(
                                $counts['customers']
                                + $counts['deposits']
                                + $counts['withdrawals']
                                + $counts['loan_payments']
                                + $counts['savings_transfers']
                                + $counts['donations']
                                + $counts['loan_requests']
                            ) }}

                        </div>

                    </div>


                    <div class="col-6 col-md-3">

                        <div class="summary-label">
                            ورود وجه
                        </div>

                        <div class="summary-value">

                            {{ number_format(
                                $totals['deposits']
                                + $totals['loan_payments']
                                + $totals['donations']
                            ) }}

                            ریال

                        </div>

                    </div>


                    <div class="col-6 col-md-3">

                        <div class="summary-label">
                            خروج وجه
                        </div>

                        <div class="summary-value">

                            {{ number_format($totals['withdrawals']) }}

                            ریال

                        </div>

                    </div>


                    <div class="col-6 col-md-3">

                        <div class="summary-label">
                            خالص عملیات
                        </div>

                        <div class="summary-value">

                            {{ number_format($netAmount) }}

                            ریال

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================
             Operations Table
             ========================================= --}}

        <div class="card border-0 shadow-sm rounded-4">

            <div class="card-header bg-white border-0 pt-4 px-4">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0">

                        <i class="bi bi-list-check me-1"></i>

                        عملیات صندوق

                    </h5>

                    <span class="text-muted small">

                        {{ number_format(
                            $counts['customers']
                            + $counts['deposits']
                            + $counts['withdrawals']
                            + $counts['loan_payments']
                            + $counts['savings_transfers']
                            + $counts['donations']
                            + $counts['loan_requests']
                        ) }}

                        عملیات

                    </span>

                </div>

            </div>


            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0 operations-table text-center">

                    <thead class="table-light">

                    <tr>

                        <th class="text-center">
                            ردیف
                        </th>

                        <th>
                            نوع عملیات
                        </th>

                        <th>
                            کد عضو
                        </th>

                        <th>
                            عضو
                        </th>

                        <th>
                            شماره حساب
                        </th>

                        <th>
                            مبلغ
                        </th>

                        <th>
                            تاریخ
                        </th>

                        <th>
                            توضیحات
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    @php
                        $row = 1;
                    @endphp


                    {{-- =====================================
                         ثبت‌نام
                         ===================================== --}}

                    @foreach($customers as $customer)

                        <tr>

                            <td class="text-center">
                                {{ $row++ }}
                            </td>

                            <td>

                                <span class="badge bg-primary-subtle text-primary">

                                    <i class="bi bi-person-plus me-1"></i>

                                    ثبت‌نام جدید

                                </span>

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $customer->customer_code ?? '—' }}

                            </td>

                            <td>

                                <div class="fw-semibold">

                                    {{ $customer->first_name }}
                                    {{ $customer->last_name }}

                                </div>

                                @if($customer->mobile)

                                    <small class="text-muted">

                                        {{ $customer->mobile }}

                                    </small>

                                @endif

                            </td>

                            <td>
                                —
                            </td>

                            <td>
                                —
                            </td>

                            <td>

                                {{ $jalaliDate(
                                    $customer->created_at,
                                    true
                                ) }}

                            </td>

                            <td>
                                ثبت عضو جدید در صندوق
                            </td>

                        </tr>

                    @endforeach


                    {{-- =====================================
                         واریز
                         ===================================== --}}

                    @foreach($deposits as $deposit)

                        <tr>

                            <td class="text-center">
                                {{ $row++ }}
                            </td>

                            <td>

                                <span class="badge bg-success-subtle text-success">

                                    <i class="bi bi-arrow-down-circle me-1"></i>

                                    واریز

                                </span>

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $deposit->account?->customer?->customer_code ?? '—' }}

                            </td>

                            <td>

                                @if($deposit->account?->customer)

                                    {{ $deposit->account->customer->first_name }}
                                    {{ $deposit->account->customer->last_name }}

                                @else

                                    —

                                @endif

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $formatAccountNumber(
                                    $deposit->account?->account_number
                                ) }}

                            </td>

                            <td class="fw-semibold">

                                {{ number_format($deposit->amount) }}

                                ریال

                            </td>

                            <td>

                                {{ $jalaliDate(
                                    $deposit->transaction_date,
                                    true
                                ) }}

                            </td>

                            <td>

                                {{ $deposit->description ?? 'واریز به حساب' }}

                            </td>

                        </tr>

                    @endforeach


                    {{-- =====================================
                         برداشت
                         ===================================== --}}

                    @foreach($withdrawals as $withdrawal)

                        <tr>

                            <td class="text-center">
                                {{ $row++ }}
                            </td>

                            <td>

                                <span class="badge bg-danger-subtle text-danger">

                                    <i class="bi bi-arrow-up-circle me-1"></i>

                                    برداشت

                                </span>

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $withdrawal->account?->customer?->customer_code ?? '—' }}

                            </td>

                            <td>

                                @if($withdrawal->account?->customer)

                                    {{ $withdrawal->account->customer->first_name }}
                                    {{ $withdrawal->account->customer->last_name }}

                                @else

                                    —

                                @endif

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $formatAccountNumber(
                                    $withdrawal->account?->account_number
                                ) }}

                            </td>

                            <td class="fw-semibold">

                                {{ number_format($withdrawal->amount) }}

                                ریال

                            </td>

                            <td>

                                {{ $jalaliDate(
                                    $withdrawal->paid_at,
                                    true
                                ) }}

                            </td>

                            <td>
                                برداشت پرداخت‌شده
                            </td>

                        </tr>

                    @endforeach


                    {{-- =====================================
                         پرداخت قسط
                         ===================================== --}}

                    @foreach($loanPayments as $payment)

                        <tr>

                            <td class="text-center">
                                {{ $row++ }}
                            </td>

                            <td>

                                <span class="badge bg-warning-subtle text-warning-emphasis">

                                    <i class="bi bi-credit-card me-1"></i>

                                    پرداخت قسط

                                </span>

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $payment->loan?->customer?->customer_code ?? '—' }}

                            </td>

                            <td>

                                @if($payment->loan?->customer)

                                    {{ $payment->loan->customer->first_name }}
                                    {{ $payment->loan->customer->last_name }}

                                @else

                                    —

                                @endif

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $formatAccountNumber(
                                    $payment->loan?->customer?->accounts?->first()?->account_number
                                ) }}

                            </td>

                            <td class="fw-semibold">

                                {{ number_format($payment->amount) }}

                                ریال

                            </td>

                            <td>

                                {{ $jalaliDate(
                                    $payment->paid_at,
                                    true
                                ) }}

                            </td>

                            <td>

                                @if($payment->installment)

                                    قسط شماره
                                    {{ $payment->installment->installment_number ?? '—' }}

                                @else

                                    پرداخت قسط

                                @endif

                            </td>

                        </tr>

                    @endforeach


                    {{-- =====================================
                         واریز به حساب پس‌انداز خود
                         ===================================== --}}

                    @foreach($ownSavingsTransfers as $transfer)

                        <tr>

                            <td class="text-center">
                                {{ $row++ }}
                            </td>

                            <td>

                                <span class="badge bg-info-subtle text-info-emphasis">

                                    <i class="bi bi-wallet2 me-1"></i>

                                    واریز به پس‌انداز خود

                                </span>

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $transfer->receiver?->customer_code ?? '—' }}

                            </td>

                            <td>

                                @if($transfer->receiver)

                                    <div class="fw-semibold">

                                        {{ $transfer->receiver->first_name }}
                                        {{ $transfer->receiver->last_name }}

                                    </div>

                                @else

                                    —

                                @endif

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $formatAccountNumber(
                                    $transfer->receiver?->accounts?->first()?->account_number
                                ) }}

                            </td>

                            <td class="fw-semibold">

                                {{ number_format($transfer->amount) }}

                                ریال

                            </td>

                            <td>

                                {{ $jalaliDate(
                                    $transfer->paid_at,
                                    true
                                ) }}

                            </td>

                            <td>

                                واریز به حساب پس‌انداز خود

                            </td>

                        </tr>

                    @endforeach


                    {{-- =====================================
                         واریز به حساب پس‌انداز دیگران
                         ===================================== --}}

                    @foreach($otherSavingsTransfers as $transfer)

                        <tr>

                            <td class="text-center">
                                {{ $row++ }}
                            </td>

                            <td>

                                <span class="badge bg-primary-subtle text-primary">

                                    <i class="bi bi-person-plus me-1"></i>

                                    واریز به پس‌انداز دیگران

                                </span>

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $transfer->receiver?->customer_code ?? '—' }}

                            </td>

                            <td>

                                @if($transfer->receiver)

                                    <div class="fw-semibold">

                                        {{ $transfer->receiver->first_name }}
                                        {{ $transfer->receiver->last_name }}

                                    </div>

                                    @if($transfer->sender)

                                        <div class="small text-muted">

                                            واریزکننده:

                                            {{ $transfer->sender->name ?? '—' }}

                                        </div>

                                    @endif

                                @else

                                    —

                                @endif

                            </td>

                            <td dir="ltr" class="text-center">

                                <div>

                                    <small class="text-muted">
                                        مبدأ:
                                    </small>

                                    {{ $formatAccountNumber(
                                        $transfer->account?->account_number
                                    ) }}

                                </div>

                                <div>

                                    <small class="text-muted">
                                        مقصد:
                                    </small>

                                    {{ $formatAccountNumber(
                                        $transfer->receiver?->accounts?->first()?->account_number
                                    ) }}

                                </div>

                            </td>

                            <td class="fw-semibold">

                                {{ number_format($transfer->amount) }}

                                ریال

                            </td>

                            <td>

                                {{ $jalaliDate(
                                    $transfer->paid_at,
                                    true
                                ) }}

                            </td>

                            <td>

                                واریز به حساب پس‌انداز عضو دیگر

                            </td>

                        </tr>

                    @endforeach


                    {{-- =====================================
                         کمک به صندوق
                         ===================================== --}}

                    @foreach($donations as $donation)

                        <tr>

                            <td class="text-center">
                                {{ $row++ }}
                            </td>

                            <td>

                                <span class="badge bg-secondary-subtle text-secondary-emphasis">

                                    <i class="bi bi-heart me-1"></i>

                                    کمک به صندوق

                                </span>

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $donation->customer?->customer_code ?? '—' }}

                            </td>

                            <td>

                                @if($donation->customer)

                                    {{ $donation->customer->first_name }}
                                    {{ $donation->customer->last_name }}

                                @else

                                    —

                                @endif

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $formatAccountNumber(
                                    $donation->customer?->accounts?->first()?->account_number
                                ) }}

                            </td>

                            <td class="fw-semibold">

                                {{ number_format($donation->amount) }}

                                ریال

                            </td>

                            <td>

                                {{ $jalaliDate(
                                    $donation->paid_at,
                                    true
                                ) }}

                            </td>

                            <td>

                                @if($donation->donationType)

                                    {{ $donation->donationType->name }}

                                @else

                                    کمک به صندوق

                                @endif

                            </td>

                        </tr>

                    @endforeach


                    {{-- =====================================
                         درخواست وام
                         ===================================== --}}

                    @foreach($loanRequests as $loanRequest)

                        <tr>

                            <td class="text-center">
                                {{ $row++ }}
                            </td>

                            <td>

                                <span class="badge bg-dark-subtle text-dark">

                                    <i class="bi bi-file-earmark-text me-1"></i>

                                    درخواست وام

                                </span>

                            </td>

                            <td dir="ltr" class="text-center">

                                {{ $loanRequest->customer?->customer_code ?? '—' }}

                            </td>

                            <td>

                                @if($loanRequest->customer)

                                    {{ $loanRequest->customer->first_name }}
                                    {{ $loanRequest->customer->last_name }}

                                @else

                                    —

                                @endif

                            </td>

                            <td>
                                —
                            </td>

                            <td>
                                —
                            </td>

                            <td>

                                {{ $jalaliDate(
                                    $loanRequest->created_at,
                                    true
                                ) }}

                            </td>

                            <td>

                                @if($loanRequest->loanType)

                                    درخواست
                                    {{ $loanRequest->loanType->name }}

                                @else

                                    درخواست وام

                                @endif

                            </td>

                        </tr>

                    @endforeach


                    {{-- =====================================
                         Empty
                         ===================================== --}}

                    @if(
                        $counts['customers'] === 0 &&
                        $counts['deposits'] === 0 &&
                        $counts['withdrawals'] === 0 &&
                        $counts['loan_payments'] === 0 &&
                        $counts['savings_transfers'] === 0 &&
                        $counts['donations'] === 0 &&
                        $counts['loan_requests'] === 0
                    )

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5 text-muted"
                            >

                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>

                                عملیاتی برای نمایش پیدا نشد.

                            </td>

                        </tr>

                    @endif

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection
