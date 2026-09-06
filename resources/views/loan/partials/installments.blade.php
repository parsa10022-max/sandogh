<div class="card shadow-sm border-0 mt-4 loan-installments-card">

    <div class="card-header bg-light loan-installments-header">

        <h6 class="mb-0 fw-bold">
            <i class="bi bi-calendar-check me-1"></i>
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

                    <thead>

                    <tr>

                        <th class="text-center" width="80">
                            قسط
                        </th>

                        <th class="text-center" width="170">
                            سررسید
                        </th>

                        <th class="text-center" width="190">
                            مبلغ
                        </th>

                        <th class="text-center" width="170">
                            وضعیت
                        </th>

                        <th class="text-center" width="160">
                            تاریخ پرداخت
                        </th>

                        <th class="text-center" width="180">
                            عملیات
                        </th>

                    </tr>

                    </thead>

                    <tbody>

                    @foreach($loan->installments as $installment)

                        <tr>

                            {{-- شماره قسط --}}
                            <td class="text-center fw-bold">

                                {{ $installment->installment_number }}

                            </td>

                            {{-- تاریخ سررسید --}}
                            <td class="text-center">

                                {{ $installment->due_date_jalali }}

                            </td>

                            {{-- مبلغ --}}
                            <td class="text-center fw-bold loan-installment-amount">

                                {{ number_format($installment->amount) }}

                                <small>ریال</small>

                            </td>

                            {{-- وضعیت --}}
                            <td class="text-center">

                                @if($installment->status->value === \App\Enums\InstallmentStatus::PAID->value)

                                    <span class="loan-installment-status loan-installment-status-paid">

                                        <i class="bi bi-check-circle"></i>

                                        پرداخت شده

                                    </span>

                                @else

                                    <span class="loan-installment-status loan-installment-status-pending">

                                        <i class="bi bi-clock"></i>

                                        در انتظار پرداخت

                                    </span>

                                @endif

                            </td>

                            {{-- تاریخ پرداخت --}}
                            <td class="text-center">

                                @if($installment->status->isPaid())

                                    {{ $installment->paid_at_jalali }}

                                @else

                                    <span class="text-muted">-</span>

                                @endif

                            </td>

                            {{-- عملیات --}}
                            <td class="text-center">

                                @if($installment->status->isPaid())

                                    <a
                                        href="{{ route('payments.success', $installment->payment) }}"
                                        class="loan-installment-action loan-installment-receipt">

                                        <span class="loan-installment-action-icon">
                                            <i class="bi bi-receipt"></i>
                                        </span>

                                        <span>
                                            مشاهده رسید
                                        </span>

                                    </a>

                                @elseif(
                                    $loop->first ||
                                    $loan->installments[$loop->index - 1]->status->value === \App\Enums\InstallmentStatus::PAID->value
                                )

                                    <form
                                        action="{{ route('payments.pay', $installment) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf

                                        <button
                                            type="submit"
                                            class="loan-installment-action loan-installment-pay">

                                            <span class="loan-installment-action-icon">
                                                <i class="bi bi-credit-card"></i>
                                            </span>

                                            <span>
                                                پرداخت
                                            </span>

                                        </button>

                                    </form>

                                @else

                                    <button
                                        type="button"
                                        class="loan-installment-action loan-installment-locked"
                                        disabled>

                                        <span class="loan-installment-action-icon">
                                            <i class="bi bi-lock"></i>
                                        </span>

                                        <span>
                                            قسط قبلی پرداخت نشده
                                        </span>

                                    </button>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                    <tfoot>

                    <tr>

                        <th colspan="2" class="text-center">

                            تعداد اقساط :
                            {{ $loan->installments->count() }}

                        </th>

                        {{-- جمع مبلغ --}}
                        <th class="text-center loan-installment-amount">

                            {{ number_format($loan->installments->sum('amount')) }}

                            <small>ریال</small>

                        </th>

                        <th colspan="3" class="text-center">

                            جمع مبلغ اقساط

                        </th>

                    </tr>

                    </tfoot>

                </table>

            </div>

        @endif

    </div>

</div>

