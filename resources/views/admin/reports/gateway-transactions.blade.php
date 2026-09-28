@extends('layouts.app')

@section('title', 'گزارش تراکنش‌های درگاه')

@section('content')

    <div class="container-fluid py-3 gateway-report">

        {{-- Header --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 no-print">

            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-credit-card-2-front me-1"></i>
                    گزارش تراکنش‌های درگاه
                </h4>

                <div class="text-muted small">
                    گزارش کلیه عملیات انجام‌شده از طریق درگاه پرداخت
                </div>
            </div>

        </div>


        {{-- Summary Cards --}}
        <div class="row g-3 mb-4 no-print">

            {{-- Total --}}
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 report-stat-card">
                    <div class="card-body">

                        <div class="d-flex align-items-center gap-3">

                            <div class="report-stat-icon bg-primary-subtle text-primary">
                                <i class="bi bi-credit-card"></i>
                            </div>

                            <div>
                                <div class="text-muted small">
                                    کل تراکنش‌ها
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ fa_number($summary['total_count']) }}
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>


            {{-- Successful --}}
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 report-stat-card">
                    <div class="card-body">

                        <div class="d-flex align-items-center gap-3">

                            <div class="report-stat-icon bg-success-subtle text-success">
                                <i class="bi bi-check-circle"></i>
                            </div>

                            <div>
                                <div class="text-muted small">
                                    موفق
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ fa_number($summary['successful_count']) }}
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>


            {{-- Failed --}}
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 report-stat-card">
                    <div class="card-body">

                        <div class="d-flex align-items-center gap-3">

                            <div class="report-stat-icon bg-danger-subtle text-danger">
                                <i class="bi bi-x-circle"></i>
                            </div>

                            <div>
                                <div class="text-muted small">
                                    ناموفق
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ fa_number($summary['failed_count']) }}
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>


            {{-- Pending --}}
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100 report-stat-card">
                    <div class="card-body">

                        <div class="d-flex align-items-center gap-3">

                            <div class="report-stat-icon bg-warning-subtle text-warning">
                                <i class="bi bi-hourglass-split"></i>
                            </div>

                            <div>
                                <div class="text-muted small">
                                    در انتظار
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ fa_number($summary['pending_count']) }}
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>


        {{-- Successful Amount --}}
        <div class="card border-0 shadow-sm mb-4 no-print">

            <div class="card-body">

                <div class="d-flex align-items-center gap-3">

                    <div class="report-stat-icon bg-info-subtle text-info">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div>

                        <div class="text-muted small">
                            مجموع مبلغ تراکنش‌های موفق
                        </div>

                        <div class="fs-4 fw-bold">
                            {{ fa_money($summary['successful_amount']) }}

                            <span class="fs-6 fw-normal text-muted">
                                ریال
                            </span>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Filters --}}
        <div class="card border-0 shadow-sm mb-4 no-print">

            <div class="card-body">

                <form
                    method="GET"
                    action="{{ route('admin.reports.gateway-transactions') }}"
                >

                    <div class="row g-3 align-items-end">

                        {{-- From --}}
                        <div class="col-md-3">

                            <label for="from" class="form-label">
                                از تاریخ
                            </label>

                            <input
                                type="text"
                                name="from"
                                id="from"
                                value="{{ $from }}"
                                class="form-control"
                                placeholder="۱۴۰۵/۰۶/۱۹"
                                autocomplete="off"
                                data-jdp
                            >

                        </div>


                        {{-- To --}}
                        <div class="col-md-3">

                            <label for="to" class="form-label">
                                تا تاریخ
                            </label>

                            <input
                                type="text"
                                name="to"
                                id="to"
                                value="{{ $to }}"
                                class="form-control"
                                placeholder="۱۴۰۵/۰۶/۱۹"
                                autocomplete="off"
                                data-jdp
                            >

                        </div>


                        {{-- Type --}}
                        <div class="col-12 col-md-4">

                            <label for="type" class="form-label fw-semibold">
                                نوع عملیات
                            </label>

                            <select
                                id="type"
                                name="type"
                                class="form-select"
                            >

                                <option value="">
                                    همه عملیات
                                </option>

                                <option
                                    value="savings_own"
                                    @selected($type === 'savings_own')
                                >
                                واریز به حساب پس‌انداز خود
                                </option>

                                <option
                                    value="savings_other"
                                    @selected($type === 'savings_other')
                                >
                                واریز به حساب پس‌انداز دیگران
                                </option>

                                <option
                                    value="installment_own"
                                    @selected($type === 'installment_own')
                                >
                                پرداخت قسط خود
                                </option>

                                <option
                                    value="installment_other"
                                    @selected($type === 'installment_other')
                                >
                                پرداخت قسط دیگران
                                </option>

                                <option
                                    value="system_help"
                                    @selected($type === 'system_help')
                                >
                                کمک سیستمی
                                </option>

                            </select>

                        </div>


                        {{-- Buttons --}}
                        <div class="col-12 col-md-2">

                            <div class="d-grid gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    <i class="bi bi-search me-1"></i>
                                    نمایش گزارش
                                </button>

                                <a
                                    href="{{ route('admin.reports.gateway-transactions') }}"
                                    class="btn btn-light border"
                                >
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    حذف فیلتر
                                </a>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- Transactions --}}
        <div class="card border-0 shadow-sm gateway-report-card">

            {{-- Table Header --}}
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 p-3 border-bottom no-print">

                <div>

                    <h5 class="fw-bold mb-1">
                        تراکنش‌های درگاه
                    </h5>

                    <div class="text-muted small">
                        {{ fa_number($transactions->count()) }}
                        تراکنش
                    </div>

                </div>


                {{-- Report Actions --}}
                <div class="d-flex flex-wrap gap-2 report-actions">

                    {{-- Excel --}}
                    <a
                        href="{{ route('admin.reports.gateway-transactions.export', request()->query()) }}"
                        class="btn btn-success"
                    >
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        خروجی Excel
                    </a>


                    {{-- Print --}}
                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        onclick="window.print()"
                    >
                        <i class="bi bi-printer me-1"></i>
                        چاپ
                    </button>


                    {{-- PDF --}}
                    <button
                        type="button"
                        class="btn btn-danger"
                        onclick="window.print()"
                    >
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        خروجی PDF
                    </button>

                </div>

            </div>


            {{-- Print Header --}}
            <div class="print-header">

                <h4>
                    گزارش تراکنش‌های درگاه
                </h4>

                <div class="print-date">
                    تاریخ چاپ:
                    {{ fa_number(
                        \Morilog\Jalali\Jalalian::now()->format('Y/m/d H:i')
                    ) }}
                </div>

            </div>


            {{-- Table --}}
            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                    <tr>

                        <th class="text-nowrap">
                            #
                        </th>

                        <th class="text-nowrap">
                            تاریخ و ساعت
                        </th>

                        <th class="text-nowrap">
                            نوع عملیات
                        </th>

                        <th class="text-nowrap">
                            پرداخت‌کننده
                        </th>

                        <th class="text-nowrap">
                            دریافت‌کننده
                        </th>

                        <th class="text-nowrap">
                            مبلغ
                        </th>

                        <th class="text-nowrap">
                            درگاه
                        </th>

                        <th class="text-nowrap">
                            کد رهگیری
                        </th>

                        <th class="text-nowrap">
                            شناسه تراکنش
                        </th>

                        <th class="text-nowrap">
                            شماره مرجع
                        </th>

                        <th class="text-nowrap">
                            وضعیت
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    @forelse($transactions as $index => $transaction)

                        <tr>

                            <td>
                                {{ fa_number($index + 1) }}
                            </td>


                            <td class="text-nowrap">

                                @if($transaction['date'])

                                    {{ fa_number(
                                        \Morilog\Jalali\Jalalian::fromCarbon(
                                            \Carbon\Carbon::parse($transaction['date'])
                                        )->format('Y/m/d')
                                    ) }}

                                    <div class="small text-muted">
                                        {{ fa_number(
                                            \Carbon\Carbon::parse($transaction['date'])->format('H:i:s')
                                        ) }}
                                    </div>

                                @else

                                    —

                                @endif

                            </td>


                            <td>

                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $transaction['type_label'] }}
                                </span>

                            </td>


                            <td>
                                {{ $transaction['payer'] }}
                            </td>


                            <td>
                                {{ $transaction['receiver'] }}
                            </td>


                            <td class="text-nowrap fw-semibold">

                                {{ fa_money($transaction['amount']) }}

                                <span class="small text-muted">
                                    ریال
                                </span>

                            </td>


                            <td>
                                {{ $transaction['gateway'] ?: '—' }}
                            </td>


                            <td class="text-nowrap">
                                {{ $transaction['tracking_code'] ?: '—' }}
                            </td>


                            <td class="text-nowrap">
                                {{ $transaction['bank_transaction_id'] ?: '—' }}
                            </td>


                            <td class="text-nowrap">
                                {{ $transaction['bank_reference_number'] ?: '—' }}
                            </td>


                            <td>

                                @if($transaction['status'] === 'paid')

                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        موفق
                                    </span>

                                @elseif($transaction['status'] === 'failed')

                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="bi bi-x-circle me-1"></i>
                                        ناموفق
                                    </span>

                                @else

                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        در انتظار
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="11"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i class="bi bi-credit-card-2-front fs-1 d-block mb-3"></i>

                                    <div class="fw-semibold mb-1">
                                        تراکنشی پیدا نشد
                                    </div>

                                    <div class="small">
                                        با فیلترهای انتخاب‌شده تراکنشی ثبت نشده است.
                                    </div>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>


                    {{-- Total --}}
                    @if($transactions->count() > 0)

                        <tfoot>

                        <tr class="table-light fw-bold">

                            <td
                                colspan="5"
                                class="text-end"
                            >
                                جمع کل
                            </td>

                            <td class="text-nowrap">

                                {{ fa_money($transactions->sum('amount')) }}

                                <span class="small text-muted">
                                    ریال
                                </span>

                            </td>

                            <td colspan="5"></td>

                        </tr>

                        </tfoot>

                    @endif

                </table>

            </div>

        </div>

    </div>

@endsection

