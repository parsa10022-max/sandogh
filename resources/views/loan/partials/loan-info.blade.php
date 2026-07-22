<div class="card shadow-sm border-0">

    <div class="card-header bg-light">

        <h6 class="mb-0 fw-bold">

            اطلاعات وام

        </h6>

    </div>

    <div class="card-body">

        <div class="row g-3">

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        مشتری :
                    </span>

                    <span class="loan-info-value">

                        {{ $loan->customer->display_name }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        نوع وام :
                    </span>

                    <span class="loan-info-value">

                        {{ $loan->loanType->name }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        شماره وام :
                    </span>

                    <span class="loan-info-value loan-number-display">

                        {{ $loan->full_loan_number }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        مبلغ وام :
                    </span>

                    <span class="loan-info-value">

                        {{ number_format($loan->loan_amount) }} ریال

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        مبلغ هر قسط :
                    </span>

                    <span class="loan-info-value text-success">

                        {{ number_format($loan->installment_amount) }} ریال

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        تعداد اقساط :
                    </span>

                    <span class="loan-info-value">

                        {{ $loan->installment_count }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        دوره پرداخت :
                    </span>

                    <span class="loan-info-value">

                        {{ $loan->installment_interval->label() }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        تاریخ ثبت :
                    </span>

                    <span class="loan-info-value">

                        {{ $loan->start_date_jalali }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        اولین سررسید :
                    </span>

                    <span class="loan-info-value">

                        {{ $loan->first_due_date_jalali }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        آخرین سررسید :
                    </span>

                    <span class="loan-info-value">

                        {{ $loan->last_due_date_jalali }}

                    </span>

                </div>

            </div>

        </div>

        <hr class="my-4">

        <div>

            <div class="loan-info-title mb-2">

                توضیحات :

            </div>

            <div class="border rounded p-3 bg-light">

                {{ $loan->description ?: '-' }}

            </div>

        </div>

    </div>

</div>
