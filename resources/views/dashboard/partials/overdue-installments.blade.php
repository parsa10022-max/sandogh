<div class="card border-0 shadow-sm mt-4">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <h6 class="mb-0 fw-bold">
            اقساط معوق
        </h6>

        <div>

            <span class="badge bg-warning text-dark me-2">

                {{ $dashboard['overdueInstallments']->count() }}

                قسط

            </span>

            <span class="badge bg-danger">

                {{ number_format($dashboard['overdueInstallments']->sum('amount')) }}

                ریال

            </span>

        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

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

                    <td>
                        {{ $installment->loan->loan_number }}

                        -
                        {{ $installment->loan->loanType->prefix }}

                    </td>

                    <td>

                        {{ $installment->loan->customer->full_name }}

                    </td>

                    <td>

                        {{ $installment->due_date_jalali }}

                    </td>

                    <td>

                        {{ number_format($installment->amount) }}

                    </td>

                    <td>

                        <span class="badge bg-{{ $color }} ">

                            {{ $installment->overdue_days }}

                            روز

                        </span>

                    </td>

                    <td class="text-center">

                        @if (!$installment->payment)

                            <a href="{{ route('loans.show', $installment->loan) }}"
                               class="btn btn-sm btn-outline-success">

                                <i class="bi bi-credit-card"></i>

                                پرداخت

                            </a>

                        @endif

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" class="text-center py-4 text-muted">

                        قسط معوقی وجود ندارد.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>
