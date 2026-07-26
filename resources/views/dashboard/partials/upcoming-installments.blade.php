<div class="card border-0 shadow-sm mt-4">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <h6 class="mb-0 fw-bold">
            سررسیدهای ۷ روز آینده
        </h6>

        <span class="badge bg-primary">

            {{ $dashboard['upcomingInstallments']->count() }}

            قسط

        </span>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

            <tr>

                <th>وام</th>

                <th>عضو</th>

                <th>سررسید</th>

                <th>مبلغ</th>

                <th>باقیمانده</th>

            </tr>

            </thead>

            <tbody>

            @forelse($dashboard['upcomingInstallments'] as $installment)

                <tr>

                    <td>

                        {{ $installment->loan->loanType->prefix }}
                        -
                        {{ $installment->loan->loan_number }}

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

                        {{ now()->startOfDay()->diffInDays($installment->due_date) }}
                        روز

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5" class="text-center py-4 text-muted">

                        سررسیدی در ۷ روز آینده وجود ندارد.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>
