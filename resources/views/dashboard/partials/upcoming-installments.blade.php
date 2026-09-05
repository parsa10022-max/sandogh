<div class="card dashboard-upcoming-installments-card">

    {{-- Header --}}
    <div class="card-header dashboard-upcoming-installments-header">

        <div class="dashboard-upcoming-installments-title">

            <div class="dashboard-section-icon dashboard-upcoming-icon">
                <i class="bi bi-calendar-event"></i>
            </div>

            <div>
                <h6 class="mb-1 fw-bold">
                    سررسیدهای ۷ روز آینده
                </h6>

                <small class="text-muted">
                    اقساطی که به‌زودی سررسید می‌شوند
                </small>
            </div>

        </div>

        <span class="dashboard-upcoming-count">
            <i class="bi bi-list-check"></i>

            {{ $dashboard['upcomingInstallments']->count() }}

            قسط
        </span>

    </div>


    {{-- Table --}}
    <div class="table-responsive dashboard-upcoming-table-wrapper">

        <table class="table dashboard-upcoming-table align-middle mb-0">

            <thead>
            <tr>

                <th>
                    وام
                </th>

                <th>
                    عضو
                </th>

                <th>
                    سررسید
                </th>

                <th>
                    مبلغ
                </th>

                <th>
                    زمان باقی‌مانده
                </th>

            </tr>
            </thead>


            <tbody>

            @forelse($dashboard['upcomingInstallments'] as $installment)

                <tr>

                    {{-- Loan --}}
                    <td>

                        <div class="dashboard-upcoming-loan">

                            <span class="dashboard-upcoming-loan-prefix">
                                {{ $installment->loan->loanType->prefix }}
                            </span>

                            <span class="dashboard-upcoming-loan-number">
                                {{ $installment->loan->loan_number }}
                            </span>

                        </div>

                    </td>


                    {{-- Customer --}}
                    <td>

                        <div class="dashboard-upcoming-customer">

                            <div class="dashboard-upcoming-customer-icon">
                                <i class="bi bi-person"></i>
                            </div>

                            <span>
                                {{ $installment->loan->customer->full_name }}
                            </span>

                        </div>

                    </td>


                    {{-- Due Date --}}
                    <td>

                        <span class="dashboard-upcoming-date">

                            <i class="bi bi-calendar3"></i>

                            {{ $installment->due_date_jalali }}

                        </span>

                    </td>


                    {{-- Amount --}}
                    <td>

                        <div class="dashboard-upcoming-amount">

                            {{ number_format($installment->amount) }}

                            <small>
                                ریال
                            </small>

                        </div>

                    </td>


                    {{-- Remaining Days --}}
                    <td>

                        @php
                            $remainingDays = now()
                                ->startOfDay()
                                ->diffInDays($installment->due_date);
                        @endphp

                        <span class="dashboard-upcoming-days">

                            <i class="bi bi-clock"></i>

                            {{ $remainingDays }}

                            روز

                        </span>

                    </td>

                </tr>


            @empty

                <tr>

                    <td colspan="5">

                        <div class="dashboard-upcoming-empty-state">

                            <div class="dashboard-upcoming-empty-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>

                            <div class="fw-bold">
                                سررسیدی در ۷ روز آینده وجود ندارد
                            </div>

                            <small class="text-muted">
                                در حال حاضر قسط نزدیکی برای پرداخت وجود ندارد.
                            </small>

                        </div>

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>
