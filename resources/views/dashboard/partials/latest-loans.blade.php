<div class="card dashboard-latest-loans-card">

    {{-- Header --}}
    <div class="card-header dashboard-latest-loans-header">

        <div class="dashboard-latest-loans-title">

            <div class="dashboard-section-icon dashboard-loans-icon">
                <i class="bi bi-cash-stack"></i>
            </div>

            <div>
                <h6 class="mb-1 fw-bold">
                    آخرین وام‌ها
                </h6>

                <small class="text-muted">
                    آخرین وام‌های ثبت‌شده در صندوق
                </small>
            </div>

        </div>

        <span class="dashboard-latest-loans-count">
            <i class="bi bi-list-check"></i>

            {{ $dashboard['latestLoans']->count() }}

            وام
        </span>

    </div>


    {{-- Table --}}
    <div class="table-responsive dashboard-latest-loans-table-wrapper">

        <table class="table dashboard-latest-loans-table align-middle mb-0">

            <thead>
            <tr>
                <th>شماره وام</th>
                <th>عضو</th>
                <th>نوع وام</th>
                <th>مبلغ</th>
                <th>وضعیت</th>
                <th class="text-center">عملیات</th>
            </tr>
            </thead>

            <tbody>

            @forelse($dashboard['latestLoans'] as $loan)

                <tr>

                    {{-- Loan Number --}}
                    <td>
                        <div class="dashboard-loan-number">
                            <span>
                                {{ $loan->loan_number }}
                            </span>
                            <span class="dashboard-loan-prefix">
                                {{ $loan->loanType->prefix }}
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
                                {{ $loan->customer->full_name }}
                            </span>

                        </div>
                    </td>


                    {{-- Loan Type --}}
                    <td>
                        <span class="dashboard-loan-type">
                            {{ $loan->loanType->name }}
                        </span>
                    </td>


                    {{-- Amount --}}
                    <td>
                        <span class="dashboard-loan-amount">

                            {{ number_format($loan->loan_amount) }}

                            <small>ریال</small>

                        </span>
                    </td>


                    {{-- Status --}}
                    <td>

                        <span class="badge dashboard-loan-status
                            dashboard-loan-status-{{ $loan->status->value }}">

                            {{ $loan->status->label() }}

                        </span>

                    </td>


                    {{-- Action --}}
                    <td class="text-center">

                        <a href="{{ route('loans.show', $loan) }}"
                           class="btn btn-sm dashboard-loan-action">

                            <i class="bi bi-eye"></i>

                            مشاهده

                        </a>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6">

                        <div class="dashboard-empty-state">

                            <div class="dashboard-empty-icon">
                                <i class="bi bi-cash-stack"></i>
                            </div>

                            <div class="fw-bold">
                                وامی ثبت نشده است
                            </div>

                            <small class="text-muted">
                                هنوز هیچ وامی در سیستم ثبت نشده است.
                            </small>

                        </div>

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

