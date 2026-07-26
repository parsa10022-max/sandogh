<div class="card border-0 shadow-sm mt-4">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <h6 class="mb-0 fw-bold">

            آخرین پرداخت‌ها

        </h6>

        <span class="badge bg-success">

            {{ $dashboard['latestPayments']->count() }}

            پرداخت

        </span>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

            <tr>

                <th>وام</th>

                <th>عضو</th>

                <th>مبلغ</th>

                <th>تاریخ پرداخت</th>

                <th>کد رهگیری</th>

            </tr>

            </thead>

            <tbody>

            @forelse($dashboard['latestPayments'] as $payment)

                <tr>

                    <td>

                        {{ $payment->loan->loanType->prefix }}
                        -
                        {{ $payment->loan->loan_number }}

                    </td>

                    <td>

                        {{ $payment->loan->customer->full_name }}

                    </td>

                    <td>

                        {{ number_format($payment->amount) }}

                    </td>

                    <td>

                        {{ app(\App\Services\Date\JalaliDateService::class)->toJalali($payment->paid_at) }}

                    </td>

                    <td>

                        {{ $payment->tracking_code }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="5" class="text-center py-4 text-muted">

                        پرداختی ثبت نشده است.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>
