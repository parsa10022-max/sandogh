@extends('layouts.app')

@section('title', 'گزارش پرداخت اقساط از حساب پس‌انداز')

@section('content')
    <div class="container-fluid py-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h4 class="fw-bold mb-1">گزارش پرداخت اقساط از حساب پس‌انداز</h4>
                <div class="text-muted small">
                    پرداخت اقساط توسط مشتری از حساب پس‌انداز خودش
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer"></i>
                چاپ گزارش
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.savings-installment-payments') }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-4">
                            <label for="from_date" class="form-label">از تاریخ</label>
                            <input
                                type="text"
                                id="from_date"
                                name="from_date"
                                class="form-control"
                                value="{{ request('from_date') }}"
                                placeholder="مثلاً 1405/08/01"
                                dir="ltr"
                            >
                        </div>

                        <div class="col-md-4">
                            <label for="to_date" class="form-label">تا تاریخ</label>
                            <input
                                type="text"
                                id="to_date"
                                name="to_date"
                                class="form-control"
                                value="{{ request('to_date') }}"
                                placeholder="مثلاً 1405/08/30"
                                dir="ltr"
                            >
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i>
                                جستجو
                            </button>

                            <a href="{{ route('admin.reports.savings-installment-payments') }}"
                               class="btn btn-outline-secondary">
                                پاک کردن فیلتر
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">فهرست پرداخت‌ها</h6>
                    <span class="badge bg-primary-subtle text-primary">
                    {{ number_format($payments->total()) }} پرداخت
                </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center">
                        <thead class="table-light">
                        <tr>
                            <th>ردیف</th>
                            <th>تاریخ پرداخت</th>
                            <th>نام و کد مشتری</th>
                            <th>شماره حساب پس‌انداز</th>
                            <th>شماره وام</th>
                            <th>شماره قسط</th>
                            <th>مبلغ پرداختی</th>
                            <th>کد رهگیری</th>
                        </tr>
                        </thead>

                        <tbody>
                        @forelse($payments as $payment)
                            @php
                                $customer = $payment->loan?->customer;

                                $savingAccount = $customer?->accounts
                                    ->firstWhere('account_type', \App\Enums\AccountType::SAVING->value);
                            @endphp

                            <tr>
                                <td>{{ fa_number($payments->firstItem() + $loop->index) }}</td>

                                <td>
                                    {{ $payment->paid_at
                                        ? \Morilog\Jalali\Jalalian::fromCarbon($payment->paid_at)->format('Y/m/d H:i')
                                        : '—' }}
                                </td>

                                <td>
                                    @if($customer)
                                        <div class="fw-semibold">{{ $customer->full_name }}</div>
                                        <small class="text-muted">
                                            {{ fa_number($customer->customer_code ?? $customer->id) }}
                                        </small>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td dir="ltr">
                                    {{ $savingAccount?->account_number ?? '—' }}
                                </td>

                                <td>
                                    {{ $payment->loan?->full_loan_number ?? '—' }}
                                </td>

                                <td>
                                    {{ fa_number($payment->installment?->installment_number ?? '—') }}
                                </td>

                                <td class="fw-semibold text-success">
                                    {{ fa_money($payment->amount) }}
                                </td>

                                <td dir="ltr">
                                    {{ $payment->tracking_code ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    پرداختی با شرایط انتخاب‌شده یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $payments->links() }}
                </div>

            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        @media print {
            .sidebar,
            nav,
            header,
            form,
            button,
            .pagination {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .table {
                font-size: 11px;
            }
        }
    </style>
@endpush
