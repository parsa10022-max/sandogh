<div class="loan-info-card">

    {{-- =========================================
         HEADER
    ========================================== --}}

    <div class="loan-info-card-header">

        <div class="loan-info-card-title">

            <span class="loan-info-card-icon">
                <i class="bi bi-info-circle"></i>
            </span>

            <span>
                اطلاعات وام
            </span>

        </div>

    </div>


    {{-- =========================================
         BODY
    ========================================== --}}

    <div class="loan-info-card-body">

        <div class="row g-3">

            {{-- مشتری --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        مشتری
                    </span>

                    <span class="loan-info-value">
                        {{ $loan->customer->display_name }}
                    </span>

                </div>

            </div>


            {{-- نوع وام --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        نوع وام
                    </span>

                    <span class="loan-info-value">
                        {{ $loan->loanType->name }}
                    </span>

                </div>

            </div>


            {{-- شماره وام --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        شماره وام
                    </span>

                    <span class="loan-info-value loan-number-display">
                        {{ $loan->full_loan_number }}
                    </span>

                </div>

            </div>


            {{-- مبلغ وام --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        مبلغ وام
                    </span>

                    <span class="loan-info-value loan-money-value">
                        {{ number_format($loan->loan_amount) }}
                        <small>ریال</small>
                    </span>

                </div>

            </div>


            {{-- مبلغ هر قسط --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        مبلغ هر قسط
                    </span>

                    <span class="loan-info-value loan-money-value loan-installment-value">
                        {{ number_format($loan->installment_amount) }}
                        <small>ریال</small>
                    </span>

                </div>

            </div>


            {{-- تعداد اقساط --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        تعداد اقساط
                    </span>

                    <span class="loan-info-value">
                        {{ $loan->installment_count }}
                    </span>

                </div>

            </div>


            {{-- دوره پرداخت --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        دوره پرداخت
                    </span>

                    <span class="loan-info-value">
                        {{ $loan->installment_interval->label() }}
                    </span>

                </div>

            </div>


            {{-- تاریخ ثبت --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        تاریخ ثبت
                    </span>

                    <span class="loan-info-value">
                        {{ $loan->start_date_jalali }}
                    </span>

                </div>

            </div>


            {{-- اولین سررسید --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        اولین سررسید
                    </span>

                    <span class="loan-info-value">
                        {{ $loan->first_due_date_jalali }}
                    </span>

                </div>

            </div>


            {{-- آخرین سررسید --}}
            <div class="col-12 col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">
                        آخرین سررسید
                    </span>

                    <span class="loan-info-value">
                        {{ $loan->last_due_date_jalali }}
                    </span>

                </div>

            </div>

        </div>

    </div>

</div>
