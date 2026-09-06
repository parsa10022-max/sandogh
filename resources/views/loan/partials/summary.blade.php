<div class="loan-summary-card">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="loan-summary-header">

        <div class="loan-summary-title">

            <span class="loan-summary-title-icon">
                <i class="bi bi-bar-chart-line"></i>
            </span>

            <span>
                خلاصه بازپرداخت
            </span>

        </div>

    </div>


    {{-- =====================================================
         MAIN SUMMARY
    ====================================================== --}}

    <div class="loan-summary-body">

        <div class="row g-3">


            {{-- مبلغ کل وام --}}
            <div class="col-12 col-md-6 col-xl-3">

                <div class="loan-summary-item">

                    <div class="loan-summary-item-icon">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div class="loan-summary-item-content">

                        <span class="loan-summary-label">
                            مبلغ کل وام
                        </span>

                        <strong class="loan-summary-value">
                            {{ number_format($loan->loan_amount) }}
                            <small>ریال</small>
                        </strong>

                    </div>

                </div>

            </div>


            {{-- تعداد کل اقساط --}}
            <div class="col-12 col-md-6 col-xl-3">

                <div class="loan-summary-item">

                    <div class="loan-summary-item-icon">
                        <i class="bi bi-list-ol"></i>
                    </div>

                    <div class="loan-summary-item-content">

                        <span class="loan-summary-label">
                            تعداد کل اقساط
                        </span>

                        <strong class="loan-summary-value">
                            {{ $loan->installment_count }}
                            <small>قسط</small>
                        </strong>

                    </div>

                </div>

            </div>


            {{-- اقساط پرداخت شده --}}
            <div class="col-12 col-md-6 col-xl-3">

                <div class="loan-summary-item loan-summary-item-success">

                    <div class="loan-summary-item-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>

                    <div class="loan-summary-item-content">

                        <span class="loan-summary-label">
                            اقساط پرداخت شده
                        </span>

                        <strong class="loan-summary-value">
                            {{ $loan->installments
                                ->where('status', \App\Enums\InstallmentStatus::PAID)
                                ->count()
                            }}
                            <small>قسط</small>
                        </strong>

                    </div>

                </div>

            </div>


            {{-- اقساط باقی مانده --}}
            <div class="col-12 col-md-6 col-xl-3">

                <div class="loan-summary-item loan-summary-item-warning">

                    <div class="loan-summary-item-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>

                    <div class="loan-summary-item-content">

                        <span class="loan-summary-label">
                            اقساط باقی مانده
                        </span>

                        <strong class="loan-summary-value">
                            {{ $loan->installments
                                ->where(
                                    'status',
                                    '!=',
                                    \App\Enums\InstallmentStatus::PAID
                                )
                                ->count()
                            }}
                            <small>قسط</small>
                        </strong>

                    </div>

                </div>

            </div>


            {{-- مبلغ پرداخت شده --}}
            <div class="col-12 col-md-6 col-xl-3">

                <div class="loan-summary-item loan-summary-item-success">

                    <div class="loan-summary-item-icon">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <div class="loan-summary-item-content">

                        <span class="loan-summary-label">
                            مبلغ پرداخت شده
                        </span>

                        <strong class="loan-summary-value">

                            {{ number_format(
                                $loan->installments
                                    ->where(
                                        'status',
                                        \App\Enums\InstallmentStatus::PAID
                                    )
                                    ->sum('amount')
                            ) }}

                            <small>ریال</small>

                        </strong>

                    </div>

                </div>

            </div>


            {{-- مانده بدهی --}}
            <div class="col-12 col-md-6 col-xl-3">

                <div class="loan-summary-item loan-summary-item-danger">

                    <div class="loan-summary-item-icon">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>

                    <div class="loan-summary-item-content">

                        <span class="loan-summary-label">
                            مانده بدهی
                        </span>

                        <strong class="loan-summary-value">

                            {{ number_format(
                                $loan->loan_amount -
                                $loan->installments
                                    ->where(
                                        'status',
                                        \App\Enums\InstallmentStatus::PAID
                                    )
                                    ->sum('amount')
                            ) }}

                            <small>ریال</small>

                        </strong>

                    </div>

                </div>

            </div>


            {{-- اولین سررسید --}}
            <div class="col-12 col-md-6 col-xl-3">

                <div class="loan-summary-item">

                    <div class="loan-summary-item-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>

                    <div class="loan-summary-item-content">

                        <span class="loan-summary-label">
                            اولین سررسید
                        </span>

                        <strong class="loan-summary-value loan-summary-date">
                            {{ $loan->first_due_date_jalali }}
                        </strong>

                    </div>

                </div>

            </div>


            {{-- آخرین سررسید --}}
            <div class="col-12 col-md-6 col-xl-3">

                <div class="loan-summary-item">

                    <div class="loan-summary-item-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div class="loan-summary-item-content">

                        <span class="loan-summary-label">
                            آخرین سررسید
                        </span>

                        <strong class="loan-summary-value loan-summary-date">
                            {{ $loan->last_due_date_jalali }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
             NEXT INSTALLMENT + PROGRESS
        ====================================================== --}}

        <div class="row g-3 mt-1">


            {{-- قسط بعدی --}}
            <div class="col-12 col-lg-6">

                @php
                    $nextInstallment = $loan->installments
                        ->where(
                            'status',
                            '!=',
                            \App\Enums\InstallmentStatus::PAID
                        )
                        ->sortBy('installment_number')
                        ->first();
                @endphp

                <div class="loan-summary-detail-card">

                    <div class="loan-summary-detail-header">

                        <div class="loan-summary-detail-title">

                            <span class="loan-summary-detail-icon">
                                <i class="bi bi-calendar2-check"></i>
                            </span>

                            <span>
                                قسط بعدی
                            </span>

                        </div>

                    </div>


                    <div class="loan-summary-detail-body">

                        @if($nextInstallment)

                            <div class="loan-next-installment">

                                <div class="loan-next-row">

                                    <span>
                                        شماره قسط
                                    </span>

                                    <strong>
                                        {{ $nextInstallment->installment_number }}
                                    </strong>

                                </div>


                                <div class="loan-next-row">

                                    <span>
                                        مبلغ
                                    </span>

                                    <strong class="loan-next-amount">

                                        {{ number_format($nextInstallment->amount) }}

                                        <small>ریال</small>

                                    </strong>

                                </div>


                                <div class="loan-next-row">

                                    <span>
                                        سررسید
                                    </span>

                                    <strong>
                                        {{ $nextInstallment->due_date_jalali ?? $nextInstallment->due_date }}
                                    </strong>

                                </div>

                            </div>

                        @else

                            <div class="loan-summary-completed">

                                <span class="loan-summary-completed-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </span>

                                <span>
                                    وام تسویه شده است
                                </span>

                            </div>

                        @endif

                    </div>

                </div>

            </div>


            {{-- پیشرفت بازپرداخت --}}
            <div class="col-12 col-lg-6">

                @php

                    $paidCount = $loan->installments
                        ->where(
                            'status',
                            \App\Enums\InstallmentStatus::PAID
                        )
                        ->count();

                    $progress = $loan->installment_count > 0
                        ? round(
                            ($paidCount / $loan->installment_count) * 100
                        )
                        : 0;

                @endphp


                <div class="loan-summary-detail-card">

                    <div class="loan-summary-detail-header">

                        <div class="loan-summary-detail-title">

                            <span class="loan-summary-detail-icon">
                                <i class="bi bi-graph-up"></i>
                            </span>

                            <span>
                                پیشرفت بازپرداخت
                            </span>

                        </div>

                    </div>


                    <div class="loan-summary-detail-body">

                        <div class="loan-progress-top">

                            <strong>
                                {{ $progress }}٪
                            </strong>

                            <span>
                                {{ $paidCount }}
                                از
                                {{ $loan->installment_count }}
                                قسط
                            </span>

                        </div>


                        <div class="loan-progress">

                            <div
                                class="loan-progress-bar"
                                role="progressbar"
                                style="width: {{ $progress }}%;"
                                aria-valuenow="{{ $progress }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>

                        </div>


                        <div class="loan-progress-description">

                            {{ $paidCount }}
                            قسط از
                            {{ $loan->installment_count }}
                            قسط پرداخت شده است.

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
