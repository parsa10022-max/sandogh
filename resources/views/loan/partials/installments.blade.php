<div class="card shadow-sm border-0 mt-4">

    <div class="card-header bg-light">

        <h6 class="mb-0 fw-bold">

            برنامه اقساط

        </h6>

    </div>

    <div class="card-body p-0">

        @if($loan->installments->isEmpty())

            <div class="text-center text-muted py-5">

                <i class="bi bi-calendar-x fs-2 d-block mb-3"></i>

                هنوز برنامه اقساطی برای این وام ثبت نشده است.

            </div>

        @else

            <div class="table-responsive">

                <table class="table table-hover table-sm align-middle mb-0 loan-installments-table">

                    <thead class="table-light">

                    <tr>

                        <th class="text-center" width="80">
                            قسط
                        </th>

                        <th class="text-center" width="170">
                            سررسید
                        </th>

                        <th class="text-end">
                            مبلغ
                        </th>

                        <th class="text-center" width="170">
                            وضعیت
                        </th>

                    </tr>

                    </thead>

                    <tbody>

                    @foreach($loan->installments as $installment)

                        <tr>

                            <td class="text-center fw-bold">

                                {{ $installment->installment_number }}

                            </td>

                            <td class="text-center">

                                {{ $installment->due_date_jalali }}

                            </td>

                            <td class="text-end fw-bold">

                                {{ number_format($installment->amount) }}

                                ریال

                            </td>

                            <td class="text-center">

                                @if($installment->is_paid)

                                    <span class="badge bg-success">

                                            پرداخت شده

                                        </span>

                                @else

                                    <span class="badge bg-warning text-dark">

                                            در انتظار پرداخت

                                        </span>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                    <tfoot class="table-light">

                    <tr>

                        <th colspan="2">

                            تعداد اقساط :

                            {{ $loan->installments->count() }}

                        </th>

                        <th class="text-end">

                            {{ number_format(

                                $loan->installments->sum('amount')

                            ) }}

                            ریال

                        </th>

                        <th class="text-center">

                            جمع مبلغ اقساط

                        </th>

                    </tr>

                    </tfoot>

                </table>

            </div>

        @endif

    </div>

</div>
