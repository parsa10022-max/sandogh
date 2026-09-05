<div class="card dashboard-latest-payments-card">

    {{-- Header --}}
    <div class="card-header dashboard-latest-payments-header">

        <div class="dashboard-latest-payments-title">

            <div class="dashboard-section-icon dashboard-payments-icon">
                <i class="bi bi-credit-card"></i>
            </div>

            <div>
                <h6 class="mb-1 fw-bold">
                    آخرین پرداخت‌ها
                </h6>

                <small class="text-muted">
                    آخرین اقساط پرداخت‌شده
                </small>
            </div>

        </div>

        <span class="dashboard-latest-payments-count">
            <i class="bi bi-check-circle"></i>

            {{ $dashboard['latestPayments']->count() }}

            پرداخت
        </span>

    </div>


    {{-- Table --}}
    <div class="table-responsive dashboard-latest-payments-table-wrapper">

        <table class="table dashboard-latest-payments-table align-middle mb-0">

            <thead>
            <tr>

                <th>
                    وام
                </th>

                <th>
                    عضو
                </th>

                <th>
                    مبلغ
                </th>

                <th>
                    تاریخ پرداخت
                </th>

                <th>
                    کد رهگیری
                </th>

            </tr>
            </thead>


            <tbody>

            @forelse($dashboard['latestPayments'] as $payment)

                <tr>

                    {{-- Loan --}}
                    <td>

                        <div class="dashboard-payment-loan">

                            <span class="dashboard-payment-loan-prefix">
                                {{ $payment->loan->loanType->prefix }}
                            </span>

                            <span class="dashboard-payment-loan-number">
                                {{ $payment->loan->loan_number }}
                            </span>

                        </div>

                    </td>


                    {{-- Customer --}}
                    <td>

                        <div class="dashboard-payment-customer">

                            <div class="dashboard-payment-customer-icon">
                                <i class="bi bi-person"></i>
                            </div>

                            <span>
                                {{ $payment->loan->customer->full_name }}
                            </span>

                        </div>

                    </td>


                    {{-- Amount --}}
                    <td>

                        <div class="dashboard-payment-amount">

                            {{ number_format($payment->amount) }}

                            <small>
                                ریال
                            </small>

                        </div>

                    </td>


                    {{-- Paid At --}}
                    <td>

                        <span class="dashboard-payment-date">

                            <i class="bi bi-calendar3"></i>

                            {{ app(\App\Services\Date\JalaliDateService::class)->toJalali($payment->paid_at) }}

                        </span>

                    </td>


                    {{-- Tracking Code --}}
                    <td>

                        <span class="dashboard-payment-tracking">

                            <i class="bi bi-upc-scan"></i>

                            {{ $payment->tracking_code }}

                        </span>

                    </td>

                </tr>


            @empty

                <tr>

                    <td colspan="5">

                        <div class="dashboard-payment-empty-state">

                            <div class="dashboard-payment-empty-icon">
                                <i class="bi bi-credit-card"></i>
                            </div>

                            <div class="fw-bold">
                                پرداختی ثبت نشده است
                            </div>

                            <small class="text-muted">
                                هنوز هیچ پرداختی در سیستم ثبت نشده است.
                            </small>

                        </div>

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>
