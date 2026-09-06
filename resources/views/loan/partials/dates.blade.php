{{-- =========================================================
     تاریخ‌های وام
     Design System #1
========================================================= --}}

<div class="loan-dates-card">

    {{-- Header --}}
    <div class="loan-section-header">

        <div class="loan-section-title">

            <span class="loan-section-icon">
                <i class="bi bi-calendar-event"></i>
            </span>

            <div>

                <h6 class="mb-0">
                    تاریخ‌های وام
                </h6>

                <small>
                    تاریخ ثبت وام و سررسید بازپرداخت
                </small>

            </div>

        </div>

    </div>


    {{-- Body --}}
    <div class="loan-dates-body">

        <div class="row g-3">


            {{-- تاریخ ثبت --}}
            <div class="col-12 col-md-4">

                <div class="loan-date-item">

                    <div class="loan-date-icon">
                        <i class="bi bi-calendar-plus"></i>
                    </div>

                    <div class="loan-date-content">

                        <span class="loan-date-label">
                            تاریخ ثبت وام
                        </span>

                        <strong class="loan-date-value">
                            {{ $loan->start_date_jalali ?? '-' }}
                        </strong>

                    </div>

                </div>

            </div>


            {{-- اولین سررسید --}}
            <div class="col-12 col-md-4">

                <div class="loan-date-item">

                    <div class="loan-date-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>

                    <div class="loan-date-content">

                        <span class="loan-date-label">
                            اولین سررسید
                        </span>

                        <strong class="loan-date-value">
                            {{ $loan->first_due_date_jalali ?? '-' }}
                        </strong>

                    </div>

                </div>

            </div>


            {{-- آخرین سررسید --}}
            <div class="col-12 col-md-4">

                <div class="loan-date-item">

                    <div class="loan-date-icon">
                        <i class="bi bi-calendar-range"></i>
                    </div>

                    <div class="loan-date-content">

                        <span class="loan-date-label">
                            آخرین سررسید
                        </span>

                        <strong class="loan-date-value">
                            {{ $loan->last_due_date_jalali ?? '-' }}
                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
