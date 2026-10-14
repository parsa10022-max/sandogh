@extends('layouts.app')

@section('title', 'گزارش عملیات روزانه صندوق')

@section('content')

    <div class="container-fluid daily-operations-report">

        @php
            /*
            |--------------------------------------------------------------------------
            | تبدیل تاریخ به شمسی
            |--------------------------------------------------------------------------
            */
            $jalaliDate = function ($date, $withTime = false) {
                if (!$date) {
                    return '—';
                }

                try {
                    if (!$date instanceof \Carbon\CarbonInterface) {
                        $date = \Illuminate\Support\Carbon::parse($date);
                    }

                    return fa_number(
                        \Morilog\Jalali\Jalalian::fromCarbon($date)->format(
                            $withTime ? 'Y/m/d H:i' : 'Y/m/d'
                        )
                    );
                } catch (\Throwable $e) {
                    return '—';
                }
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
                    ['-', ' ', "\u{2010}", "\u{2011}"],
                    '',
                    (string) $accountNumber
                );

                if (strlen($accountNumber) <= 4) {
                    return $accountNumber;
                }

                return substr($accountNumber, 0, 4)
                    . "\u{2011}"
                    . substr($accountNumber, 4);
            };

            /*
            |--------------------------------------------------------------------------
            | نام مشتری
            |--------------------------------------------------------------------------
            */
            $customerName = function ($customer) {
                if (!$customer) {
                    return '—';
                }

                return trim(
                    ($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')
                ) ?: '—';
            };

            /*
            |--------------------------------------------------------------------------
            | جلوگیری از خطا در صورت نبود کلید شمارش یا مبلغ
            |--------------------------------------------------------------------------
            */
            $count = fn (string $key) => (int) ($counts[$key] ?? 0);
            $total = fn (string $key) => (int) ($totals[$key] ?? 0);

            $totalOperations =
                $count('customers')
                + $count('own_savings_transfers')
                + $count('other_savings_transfers')
                + $count('own_loan_payments')
                + $count('other_loan_payments')
                + $count('donations')
                + $count('loan_requests')
                + $count('withdrawals');

            $totalSavings =
                $total('own_savings_transfers')
                + $total('other_savings_transfers');

            $totalLoanPayments =
                $total('own_loan_payments')
                + $total('other_loan_payments');
        @endphp

        {{-- سربرگ گزارش --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 report-header">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <i class="bi bi-journal-text me-1"></i>
                            گزارش عملیات مشتریان
                        </h4>
                        <div class="text-muted small">
                            گزارش عملیات ثبت‌شده در صندوق
                        </div>
                    </div>

                    <div class="d-flex gap-2 no-print">
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i>
                            چاپ گزارش
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- فیلترها --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 no-print">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.daily-operations') }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-12 col-md-2">
                            <label for="from_date" class="form-label fw-semibold">از تاریخ</label>
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
                            <label for="to_date" class="form-label fw-semibold">تا تاریخ</label>
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
                            <label for="operation_type" class="form-label fw-semibold">نوع عملیات</label>
                            <select name="operation_type" id="operation_type" class="form-select">
                                <option value="all" @selected($operationType === 'all')>همه عملیات</option>
                                <option value="registration" @selected($operationType === 'registration')>ثبت‌نام مشتری</option>
                                <option value="saving_own" @selected($operationType === 'saving_own')>واریز به پس‌انداز خود</option>
                                <option value="saving_other" @selected($operationType === 'saving_other')>واریز به پس‌انداز دیگران</option>
                                <option value="loan_payment_own" @selected($operationType === 'loan_payment_own')>پرداخت قسط از حساب خود</option>
                                <option value="loan_payment_other" @selected($operationType === 'loan_payment_other')>پرداخت قسط دیگران</option>
                                <option value="donation" @selected($operationType === 'donation')>کمک / صدقه</option>
                                <option value="loan_request" @selected($operationType === 'loan_request')>درخواست وام</option>
                                <option value="withdrawal" @selected($operationType === 'withdrawal')>برداشت از حساب</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="customer_id" class="form-label fw-semibold">عضو</label>
                            <select name="customer_id" id="customer_id" class="form-select">
                                <option value="">همه اعضا</option>
                                @foreach($customersForFilter as $customer)
                                    <option
                                        value="{{ $customer->id }}"
                                        @selected((string) $customerId === (string) $customer->id)
                                        >
                                        {{ $customer->first_name }} {{ $customer->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="form-check mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="only_confirmed"
                                    value="1"
                                    id="only_confirmed"
                                    @checked($onlyConfirmed)
                                >
                                <label class="form-check-label" for="only_confirmed">
                                    فقط تأیید حسابداری
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel me-1"></i>
                                اعمال فیلتر
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- کارت‌های خلاصه --}}
        <div class="row g-3 mb-4">

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="summary-label">ثبت‌نام مشتری</div>
                        <div class="summary-value">{{ fa_number($count('customers')) }} نفر</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="summary-label">واریز پس‌انداز خود</div>
                        <div class="summary-value">{{ fa_money($total('own_savings_transfers')) }} ریال</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="summary-label">واریز پس‌انداز دیگران</div>
                        <div class="summary-value">{{ fa_money($total('other_savings_transfers')) }} ریال</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="summary-label">پرداخت قسط خود</div>
                        <div class="summary-value">{{ fa_money($total('own_loan_payments')) }} ریال</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl-2">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <div class="summary-label">پرداخت قسط دیگران</div>
                        <div class="summary-value">{{ fa_money($total('other_loan_payments')) }} ریال</div>
                    </div>
                </div>
            </div>



            {{-- کارت جدید برداشت --}}
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card summary-card h-100 withdrawal-summary-card">
                    <div class="card-body">
                        <div class="summary-label">برداشت‌های پرداخت‌شده</div>
                        <div class="summary-value">{{ fa_money($total('withdrawals')) }} ریال</div>
                    </div>
                </div>
            </div>

        </div>

        {{-- وضعیت کلی --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <div class="row g-3 text-center">

                    <div class="col-6 col-md-3">
                        <div class="summary-label">تعداد عملیات</div>
                        <div class="summary-value">{{ fa_number($totalOperations) }}</div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="summary-label">مجموع واریز پس‌انداز</div>
                        <div class="summary-value">{{ fa_money($totalSavings) }} ریال</div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="summary-label">مجموع پرداخت اقساط</div>
                        <div class="summary-value">{{ fa_money($totalLoanPayments) }} ریال</div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="summary-label">مجموع دریافت‌ها</div>
                        <div class="summary-value">{{ fa_money($totalReceived) }} ریال</div>
                    </div>

                </div>
                <div class="small text-muted mt-3">
                    مجموع دریافت‌ها شامل برداشت‌ها نیست و از جمع مبالغ دریافت‌شده گزارش محاسبه می‌شود.
                </div>
            </div>
        </div>

        {{-- جدول عملیات --}}
        <div class="card border-0 shadow-sm rounded-4 operations-card">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-list-check me-1"></i>
                        عملیات مشتریان
                    </h5>
                    <span class="text-muted small">{{ fa_number($totalOperations) }} عملیات</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 operations-table text-center">
                    <thead class="table-light">
                    <tr>
                        <th>ردیف</th>
                        <th>نوع عملیات</th>
                        <th>کد عضو</th>
                        <th>مشخصات عملیات</th>
                        <th>شماره حساب / وام / قسط</th>
                        <th>مبلغ</th>
                        <th>تاریخ</th>
                        <th>توضیحات</th>
                    </tr>
                    </thead>

                    <tbody>
                    @php
                        $row = 1;
                    @endphp

                    {{-- ثبت‌نام مشتری --}}
                    @foreach($customers as $customer)
                        <tr>
                            <td>{{ fa_number($row++) }}</td>
                            <td><span class="badge bg-primary-subtle text-primary">ثبت‌نام مشتری</span></td>
                            <td dir="ltr">{{ $customer->customer_code ?? '—' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $customerName($customer) }}</div>
                                @if($customer->mobile)
                                    <div class="small text-muted">موبایل: {{ $customer->mobile }}</div>
                                @endif
                            </td>
                            <td>—</td>
                            <td>—</td>
                            <td>{{ $jalaliDate($customer->created_at, true) }}</td>
                            <td>ثبت عضو جدید در صندوق</td>
                        </tr>
                    @endforeach

                    {{-- واریز به پس‌انداز خود --}}
                    @foreach($ownSavingsTransfers as $transfer)
                        <tr>
                            <td>{{ fa_number($row++) }}</td>
                            <td><span class="badge bg-success-subtle text-success">واریز پس‌انداز خود</span></td>
                            <td dir="ltr">{{ $transfer->receiver?->customer_code ?? '—' }}</td>
                            <td>
                                <div class="fw-semibold">واریزکننده: {{ $customerName($transfer->sender?->customer) }}</div>
                                <div class="small text-muted mt-1">دریافت‌کننده: {{ $customerName($transfer->receiver) }}</div>
                            </td>
                            <td dir="ltr" class="account-number">
                                {{ $formatAccountNumber($transfer->account?->account_number) }}
                            </td>
                            <td class="fw-semibold">{{ fa_money($transfer->amount) }} ریال</td>
                            <td>{{ $jalaliDate($transfer->paid_at, true) }}</td>
                            <td>واریز به حساب پس‌انداز خود از طریق درگاه</td>
                        </tr>
                    @endforeach

                    {{-- واریز به پس‌انداز دیگران --}}
                    @foreach($otherSavingsTransfers as $transfer)
                        <tr>
                            <td>{{ fa_number($row++) }}</td>
                            <td><span class="badge bg-info-subtle text-info-emphasis">واریز پس‌انداز دیگران</span></td>
                            <td>
                                <div class="small">واریزکننده: <span dir="ltr">{{ $transfer->sender?->customer?->customer_code ?? '—' }}</span></div>
                                <div class="small mt-1">دریافت‌کننده: <span dir="ltr">{{ $transfer->receiver?->customer_code ?? '—' }}</span></div>
                            </td>
                            <td>
                                <div class="fw-semibold">واریزکننده: {{ $customerName($transfer->sender?->customer) }}</div>
                                <div class="small text-muted mt-1">دریافت‌کننده: {{ $customerName($transfer->receiver) }}</div>
                            </td>
                            <td dir="ltr" class="account-number">{{ $formatAccountNumber($transfer->account?->account_number) }}</td>
                            <td class="fw-semibold">{{ fa_money($transfer->amount) }} ریال</td>
                            <td>{{ $jalaliDate($transfer->paid_at, true) }}</td>
                            <td>
                                واریز به حساب {{ $customerName($transfer->receiver) }}
                                <div class="small text-muted mt-1">توسط: {{ $customerName($transfer->sender?->customer) }}</div>
                            </td>
                        </tr>
                    @endforeach

                    {{-- پرداخت قسط خود --}}
                    @foreach($ownLoanPayments as $payment)
                        <tr>
                            <td>{{ fa_number($row++) }}</td>
                            <td><span class="badge bg-warning-subtle text-warning-emphasis">پرداخت قسط خود</span></td>
                            <td dir="ltr">{{ $payment->loan?->customer?->customer_code ?? '—' }}</td>
                            <td>
                                <div class="fw-semibold">پرداخت‌کننده: {{ $customerName($payment->user?->customer) }}</div>
                                <div class="small text-muted mt-1">وام‌گیرنده: {{ $customerName($payment->loan?->customer) }}</div>
                            </td>
                            <td>
                                <div>وام: <span dir="ltr">{{ $payment->loan?->loan_number ?? '—' }}</span></div>
                                <div class="small text-muted mt-1">قسط: {{ fa_number($payment->installment?->installment_number ?? '—') }}</div>
                            </td>
                            <td class="fw-semibold">{{ fa_money($payment->amount) }} ریال</td>
                            <td>{{ $jalaliDate($payment->paid_at, true) }}</td>
                            <td>
                                پرداخت قسط {{ fa_number($payment->installment?->installment_number ?? '—') }}
                                <div class="small text-muted mt-1">پرداخت‌کننده: {{ $customerName($payment->user?->customer) }}</div>
                            </td>
                        </tr>
                    @endforeach

                    {{-- پرداخت قسط دیگران --}}
                    @foreach($otherLoanPayments as $payment)
                        <tr>
                            <td>{{ fa_number($row++) }}</td>
                            <td><span class="badge bg-warning-subtle text-warning-emphasis">پرداخت قسط دیگران</span></td>
                            <td>
                                <div class="small">پرداخت‌کننده: <span dir="ltr">{{ $payment->user?->customer?->customer_code ?? '—' }}</span></div>
                                <div class="small mt-1">وام‌گیرنده: <span dir="ltr">{{ $payment->loan?->customer?->customer_code ?? '—' }}</span></div>
                            </td>
                            <td>
                                <div class="fw-semibold">پرداخت‌کننده: {{ $customerName($payment->user?->customer) }}</div>
                                <div class="small text-muted mt-1">وام‌گیرنده: {{ $customerName($payment->loan?->customer) }}</div>
                            </td>
                            <td>
                                <div>وام: <span dir="ltr">{{ $payment->loan?->loan_number ?? '—' }}</span></div>
                                <div class="small text-muted mt-1">قسط: {{ fa_number($payment->installment?->installment_number ?? '—') }}</div>
                            </td>
                            <td class="fw-semibold">{{ fa_money($payment->amount) }} ریال</td>
                            <td>{{ $jalaliDate($payment->paid_at, true) }}</td>
                            <td>
                                پرداخت قسط {{ fa_number($payment->installment?->installment_number ?? '—') }}
                                <div class="small text-muted mt-1">پرداخت‌کننده: {{ $customerName($payment->user?->customer) }}</div>
                                <div class="small text-muted">وام‌گیرنده: {{ $customerName($payment->loan?->customer) }}</div>
                            </td>
                        </tr>
                    @endforeach

                    {{-- کمک / صدقه --}}
                    @foreach($donations as $donation)
                        <tr>
                            <td>{{ fa_number($row++) }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary-emphasis">کمک / صدقه</span></td>
                            <td dir="ltr">{{ $donation->customer?->customer_code ?? '—' }}</td>
                            <td>
                                <div class="fw-semibold">
                                    کمک‌کننده:
                                    {{ $donation->customer ? $customerName($donation->customer) : ($donation->donor_name ?: 'کمک‌کننده عمومی') }}
                                </div>
                            </td>
                            <td dir="ltr" class="account-number">{{ $formatAccountNumber($donation->account?->account_number) }}</td>
                            <td class="fw-semibold">{{ fa_money($donation->amount) }} ریال</td>
                            <td>{{ $jalaliDate($donation->created_at, true) }}</td>
                            <td>
                                کمک / صدقه از طریق درگاه
                                @if($donation->tracking_code)
                                    <div class="small text-muted mt-1">
                                        پیگیری: <span dir="ltr">{{ $donation->tracking_code }}</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    {{-- درخواست وام --}}
                    @foreach($loanRequests as $loanRequest)
                        <tr>
                            <td>{{ fa_number($row++) }}</td>
                            <td><span class="badge bg-dark-subtle text-dark">درخواست وام</span></td>
                            <td dir="ltr">{{ $loanRequest->customer?->customer_code ?? '—' }}</td>
                            <td>
                                <div class="fw-semibold">درخواست‌کننده: {{ $customerName($loanRequest->customer) }}</div>
                            </td>
                            <td>—</td>
                            <td>—</td>
                            <td>{{ $jalaliDate($loanRequest->created_at, true) }}</td>
                            <td>درخواست {{ $loanRequest->loanType?->name ?? 'وام' }}</td>
                        </tr>
                    @endforeach

                    {{-- برداشت‌های پرداخت‌شده --}}
                    @foreach($withdrawals as $withdrawal)
                        @php
                            $withdrawalCustomer = $withdrawal->account?->customer;
                        @endphp
                        <tr>
                            <td>{{ fa_number($row++) }}</td>
                            <td>
                            <span class="badge bg-danger-subtle text-danger-emphasis">
                                <i class="bi bi-cash-stack me-1"></i>
                                برداشت از حساب
                            </span>
                            </td>
                            <td dir="ltr">{{ $withdrawalCustomer?->customer_code ?? '—' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $customerName($withdrawalCustomer) }}</div>
                                @if($withdrawal->paidBy)
                                    <div class="small text-muted mt-1">
                                        پرداخت‌کننده: {{ $withdrawal->paidBy->name ?? '—' }}
                                    </div>
                                @endif
                            </td>
                            <td dir="ltr" class="account-number">
                                {{ $formatAccountNumber($withdrawal->account?->account_number) }}
                            </td>
                            <td class="fw-semibold text-danger">
                                {{ fa_money($withdrawal->amount) }} ریال
                            </td>
                            <td>{{ $jalaliDate($withdrawal->paid_at, true) }}</td>
                            <td>
                                {{ $withdrawal->description ?: 'برداشت پرداخت‌شده از حساب عضو' }}
                                @if($withdrawal->payment_tracking_code)
                                    <div class="small text-muted mt-1">
                                        کد پیگیری:
                                        <span dir="ltr">{{ $withdrawal->payment_tracking_code }}</span>
                                    </div>
                                @endif
                                @if($withdrawal->accounting_status)
                                    <div class="small text-muted mt-1">
                                        وضعیت حسابداری:
                                        {{ $withdrawal->accounting_status->label() ?? '—' }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    {{-- حالت خالی --}}
                    @if($row === 1)
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
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

    <style>
        .daily-operations-report .operations-table {
            min-width: 1050px;
            width: 100%;
            color: #334155;
            font-size: .87rem;
        }

        .daily-operations-report .operations-table th {
            font-weight: 700;
            white-space: nowrap;
        }

        .daily-operations-report .operations-table td {
            vertical-align: middle;
        }

        .daily-operations-report .operations-table span[dir="ltr"],
        .daily-operations-report .operations-table td[dir="ltr"] {
            direction: ltr;
            unicode-bidi: embed;
        }

        .daily-operations-report .operations-table span[dir="ltr"] {
            display: inline-block;
        }

        .daily-operations-report .operations-table td[dir="ltr"],
        .daily-operations-report .operations-table td.account-number,
        .daily-operations-report .operations-table td.account-number span {
            white-space: nowrap !important;
            word-break: keep-all !important;
            overflow-wrap: normal !important;
        }

        .daily-operations-report .summary-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 .125rem .5rem rgba(15, 23, 42, .06);
        }

        .daily-operations-report .summary-label {
            color: #64748b;
            font-size: .82rem;
            margin-bottom: .35rem;
        }

        .daily-operations-report .summary-value {
            color: #1e293b;
            font-weight: 700;
            font-size: 1rem;
            overflow-wrap: anywhere;
        }

        .daily-operations-report .withdrawal-summary-card {
            border-right: 3px solid #dc3545;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm;
            }

            html,
            body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .no-print,
            .navbar,
            .sidebar,
            footer {
                display: none !important;
            }

            .daily-operations-report {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .report-header,
            .summary-card {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .operations-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .daily-operations-report .operations-table {
                width: 100% !important;
                min-width: 0 !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
                font-size: 8.5pt !important;
            }

            .daily-operations-report .operations-table thead {
                display: table-header-group !important;
            }

            .daily-operations-report .operations-table tbody tr {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }

            .daily-operations-report .operations-table th,
            .daily-operations-report .operations-table td {
                padding: 4px 5px !important;
                vertical-align: middle !important;
                overflow-wrap: anywhere;
            }

            .daily-operations-report .operations-table th:nth-child(1),
            .daily-operations-report .operations-table td:nth-child(1) { width: 4% !important; }

            .daily-operations-report .operations-table th:nth-child(2),
            .daily-operations-report .operations-table td:nth-child(2) { width: 12% !important; }

            .daily-operations-report .operations-table th:nth-child(3),
            .daily-operations-report .operations-table td:nth-child(3) { width: 10% !important; }

            .daily-operations-report .operations-table th:nth-child(4),
            .daily-operations-report .operations-table td:nth-child(4) { width: 20% !important; }

            .daily-operations-report .operations-table th:nth-child(5),
            .daily-operations-report .operations-table td:nth-child(5) { width: 18% !important; }

            .daily-operations-report .operations-table th:nth-child(6),
            .daily-operations-report .operations-table td:nth-child(6) { width: 11% !important; }

            .daily-operations-report .operations-table th:nth-child(7),
            .daily-operations-report .operations-table td:nth-child(7) { width: 11% !important; }

            .daily-operations-report .operations-table th:nth-child(8),
            .daily-operations-report .operations-table td:nth-child(8) { width: 14% !important; }

            .daily-operations-report .operations-table span[dir="ltr"],
            .daily-operations-report .operations-table td[dir="ltr"] {
                direction: ltr !important;
                unicode-bidi: embed !important;
            }

            .daily-operations-report .operations-table td[dir="ltr"],
            .daily-operations-report .operations-table td.account-number,
            .daily-operations-report .operations-table td.account-number span {
                white-space: nowrap !important;
                word-break: keep-all !important;
                overflow-wrap: normal !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .daily-operations-report .operations-table th {
                background: #f3f4f6 !important;
                color: #111827 !important;
            }

            .daily-operations-report .card {
                box-shadow: none !important;
            }
        }


    </style>

@endsection
