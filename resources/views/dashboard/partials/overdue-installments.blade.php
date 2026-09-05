<div class="card dashboard-overdue-card">

    {{-- Header --}}
    <div class="card-header dashboard-overdue-header">

        <div class="dashboard-overdue-title">

            <div class="dashboard-section-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>

            <div>
                <h6 class="mb-1 fw-bold">
                    اقساط معوق
                </h6>

                <small class="text-muted">
                    اقساطی که از تاریخ سررسید گذشته‌اند
                </small>
            </div>

        </div>


        {{-- Summary --}}
        <div class="dashboard-overdue-summary">

            <span class="dashboard-overdue-count">
                <i class="bi bi-list-check"></i>

                {{ $dashboard['overdueInstallments']->count() }}

                قسط
            </span>

            <span class="dashboard-overdue-amount">
                <i class="bi bi-cash-stack"></i>

                {{ number_format($dashboard['overdueInstallments']->sum('amount')) }}

                ریال
            </span>

        </div>

    </div>


    {{-- Table --}}
    <div class="table-responsive dashboard-overdue-table-wrapper">

        <table class="table dashboard-overdue-table align-middle mb-0">

            <thead>

            <tr>
                <th>وام</th>
                <th>عضو</th>
                <th>سررسید</th>
                <th>مبلغ</th>
                <th>تأخیر</th>
                <th class="text-center">عملیات</th>
            </tr>

            </thead>


            <tbody>

            @forelse($dashboard['overdueInstallments'] as $installment)

                @php

                    $color = match (true) {

                        $installment->overdue_days <= 7 => 'warning',

                        $installment->overdue_days <= 30 => 'danger',

                        default => 'dark',

                    };

                @endphp


                <tr>

                    {{-- Loan --}}
                    <td>

                        <div class="dashboard-loan-number">

                            <span class="dashboard-loan-prefix">
                                {{ $installment->loan->loanType->prefix }}
                            </span>

                            <span>
                                {{ $installment->loan->loan_number }}
                            </span>

                        </div>

                    </td>


                    {{-- Customer --}}
                    <td>

                        <div class="dashboard-customer-name">

                            <div class="dashboard-customer-icon">
                                <i class="bi bi-person"></i>
                            </div>

                            <span>
                                {{ $installment->loan->customer->full_name }}
                            </span>

                        </div>

                    </td>


                    {{-- Due date --}}
                    <td>

                        <span class="dashboard-due-date">

                            <i class="bi bi-calendar3"></i>

                            {{ $installment->due_date_jalali }}

                        </span>

                    </td>


                    {{-- Amount --}}
                    <td>

                        <span class="dashboard-installment-amount">

                            {{ number_format($installment->amount) }}

                            <small>ریال</small>

                        </span>

                    </td>


                    {{-- Overdue days --}}
                    <td>

                        <span class="badge dashboard-overdue-badge bg-{{ $color }}">

                            <i class="bi bi-clock-history"></i>

                            {{ $installment->overdue_days }}

                            روز

                        </span>

                    </td>


                    {{-- Action --}}
                    <td class="text-center">

                        @if (!$installment->payment)

                            <a href="{{ route('loans.show', $installment->loan) }}"
                               class="btn btn-sm dashboard-overdue-action">

                                <i class="bi bi-eye"></i>

                                مشاهده وام

                            </a>

                        @else

                            <span class="dashboard-paid-label">

                                <i class="bi bi-check-circle"></i>

                                پرداخت شده

                            </span>

                        @endif

                    </td>

                </tr>


            @empty

                <tr>

                    <td colspan="6">

                        <div class="dashboard-empty-state">

                            <div class="dashboard-empty-icon">

                                <i class="bi bi-check-circle"></i>

                            </div>

                            <div class="fw-bold">
                                قسط معوقی وجود ندارد
                            </div>

                            <small class="text-muted">
                                در حال حاضر تمام اقساط در وضعیت مناسب هستند.
                            </small>

                        </div>

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>
