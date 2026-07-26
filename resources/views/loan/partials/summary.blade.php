<div class="card shadow-sm border-0 mb-4">

    <div class="card-header bg-light border-bottom">
        <h6 class="mb-0 fw-bold text-primary">
            خلاصه بازپرداخت
        </h6>
    </div>


    <div class="card-body">

        <div class="row g-3">


            {{-- مبلغ کل وام --}}
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            مبلغ کل وام
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="fw-bold fs-5">
                            {{ number_format($loan->loan_amount) }}
                            ریال
                        </div>
                    </div>

                </div>
            </div>



            {{-- تعداد کل اقساط --}}
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            تعداد کل اقساط
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="fw-bold fs-5">
                            {{ $loan->installment_count }}
                            قسط
                        </div>
                    </div>

                </div>
            </div>



            {{-- اقساط پرداخت شده --}}
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            اقساط پرداخت شده
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="fw-bold fs-5">
                            {{ $loan->installments
                                ->where('status', \App\Enums\InstallmentStatus::PAID)
                                ->count()
                            }}
                            قسط
                        </div>
                    </div>

                </div>
            </div>



            {{-- اقساط باقی مانده --}}
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            اقساط باقی مانده
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="fw-bold fs-5">
                            {{ $loan->installments
                                ->where('status', '!=', \App\Enums\InstallmentStatus::PAID)
                                ->count()
                            }}
                            قسط
                        </div>
                    </div>

                </div>
            </div>



            {{-- مبلغ پرداخت شده --}}
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            مبلغ پرداخت شده
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="fw-bold fs-5">
                            {{ number_format(
                                $loan->installments
                                ->where('status', \App\Enums\InstallmentStatus::PAID)
                                ->sum('amount')
                            ) }}
                            ریال
                        </div>
                    </div>

                </div>
            </div>



            {{-- مانده بدهی --}}
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            مانده بدهی
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="fw-bold fs-5">
                            {{ number_format(
                                $loan->loan_amount -
                                $loan->installments
                                ->where('status', \App\Enums\InstallmentStatus::PAID)
                                ->sum('amount')
                            ) }}
                            ریال
                        </div>
                    </div>

                </div>
            </div>



            {{-- اولین سررسید --}}
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            اولین سررسید
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="fw-bold fs-5">
                            {{ $loan->first_due_date_jalali }}
                        </div>
                    </div>

                </div>
            </div>



            {{-- آخرین سررسید --}}
            <div class="col-md-6 col-xl-3">
                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            آخرین سررسید
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="fw-bold fs-5">
                            {{ $loan->last_due_date_jalali }}
                        </div>
                    </div>

                </div>
            </div>


        </div>


        {{-- قسط بعدی و پیشرفت --}}
        <div class="row g-3 mt-3">


            {{-- قسط بعدی --}}
            <div class="col-md-6">

                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            قسط بعدی
                        </div>
                    </div>


                    <div class="card-body">

                        @php
                            $nextInstallment = $loan->installments
                                ->where('status', '!=', \App\Enums\InstallmentStatus::PAID)
                                ->sortBy('installment_number')
                                ->first();
                        @endphp


                        @if($nextInstallment)

                            <div>
                                قسط شماره:
                                <strong>{{ $nextInstallment->installment_number }}</strong>
                            </div>

                            <div>
                                مبلغ:
                                <strong>
                                    {{ number_format($nextInstallment->amount) }}
                                    ریال
                                </strong>
                            </div>

                            <div>
                                سررسید:
                                <strong>
                                    {{ $nextInstallment->due_date_jalali ?? $nextInstallment->due_date }}
                                </strong>
                            </div>

                        @else

                            <span class="text-success fw-bold">
                                وام تسویه شده است
                            </span>

                        @endif

                    </div>

                </div>

            </div>



            {{-- پیشرفت بازپرداخت --}}
            <div class="col-md-6">

                @php
                    $paidCount = $loan->installments
                        ->where('status', \App\Enums\InstallmentStatus::PAID)
                        ->count();

                    $progress = $loan->installment_count > 0
                        ? round(($paidCount / $loan->installment_count) * 100)
                        : 0;
                @endphp


                <div class="card shadow-sm border h-100">

                    <div class="card-header bg-light">
                        <div class="small fw-bold text-primary">
                            پیشرفت بازپرداخت
                        </div>
                    </div>


                    <div class="card-body">

                        <div class="fw-bold fs-5 mb-2">
                            {{ $progress }}٪
                        </div>


                        <div class="progress" style="height: 12px;">

                            <div class="progress-bar"
                                 role="progressbar"
                                 style="width: {{ $progress }}%">
                            </div>

                        </div>


                        <div class="small text-muted mt-2">
                            {{ $paidCount }}
                            قسط از
                            {{ $loan->installment_count }}
                            قسط پرداخت شده
                        </div>

                    </div>

                </div>

            </div>


        </div>


    </div>

</div>
