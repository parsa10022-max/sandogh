<div class="card border-0 shadow-sm mt-4">

    <div class="card-header bg-white">

        <h6 class="mb-0 fw-bold">

            آخرین وام‌ها

        </h6>

    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">

            <tr>

                <th>شماره</th>

                <th>عضو</th>

                <th>نوع وام</th>

                <th>مبلغ</th>

                <th>وضعیت</th>

                <th></th>

            </tr>

            </thead>

            <tbody>

            @forelse($dashboard['latestLoans'] as $loan)

                <tr>

                    <td>

                        {{ $loan->loanType->prefix }}-{{ $loan->loan_number }}

                    </td>

                    <td>

                        {{ $loan->customer->full_name }}

                    </td>

                    <td>

                        {{ $loan->loanType->name }}

                    </td>

                    <td>

                        {{ number_format($loan->loan_amount) }}

                    </td>

                    <td>

                        {{ $loan->status->label() }}

                    </td>

                    <td class="text-end">

                        <a href="{{ route('loans.show',$loan) }}"
                           class="btn btn-sm btn-outline-primary">

                            مشاهده

                        </a>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6"
                        class="text-center py-4 text-muted">

                        وامی ثبت نشده است.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>
