{{-- =========================================================
     LOAN CALCULATION PREVIEW
========================================================= --}}

<div id="loan-preview-card"
     class="loan-preview-card d-none">

    {{-- Header --}}
    <div class="loan-preview-card__header">

        <div class="loan-preview-card__title">

            <span class="loan-preview-card__icon">
                <i class="bi bi-calculator"></i>
            </span>

            <div>
                <h6>
                    نتیجه محاسبه وام
                </h6>

                <small>
                    اطلاعات محاسبه‌شده وام
                </small>
            </div>

        </div>

    </div>


    {{-- Body --}}
    <div class="loan-preview-card__body">

        <div class="row g-3">


            {{-- تاریخ ثبت --}}
            <div class="col-12 col-sm-6 col-lg-4">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__icon">
                        <i class="bi bi-calendar-event"></i>
                    </span>

                    <div class="loan-preview-item__content">

                        <span class="loan-preview-item__label">
                            تاریخ ثبت وام
                        </span>

                        <strong id="preview-start-date">
                            -
                        </strong>

                    </div>

                </div>

            </div>


            {{-- اولین سررسید --}}
            <div class="col-12 col-sm-6 col-lg-4">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__icon">
                        <i class="bi bi-calendar-check"></i>
                    </span>

                    <div class="loan-preview-item__content">

                        <span class="loan-preview-item__label">
                            اولین سررسید
                        </span>

                        <strong id="preview-first-date">
                            -
                        </strong>

                    </div>

                </div>

            </div>


            {{-- آخرین سررسید --}}
            <div class="col-12 col-sm-6 col-lg-4">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__icon">
                        <i class="bi bi-calendar-range"></i>
                    </span>

                    <div class="loan-preview-item__content">

                        <span class="loan-preview-item__label">
                            آخرین سررسید
                        </span>

                        <strong id="preview-last-date">
                            -
                        </strong>

                    </div>

                </div>

            </div>


            {{-- تعداد اقساط --}}
            <div class="col-12 col-sm-6 col-lg-4">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__icon">
                        <i class="bi bi-list-ol"></i>
                    </span>

                    <div class="loan-preview-item__content">

                        <span class="loan-preview-item__label">
                            تعداد اقساط
                        </span>

                        <strong id="preview-count">
                            -
                        </strong>

                    </div>

                </div>

            </div>


            {{-- مبلغ هر قسط --}}
            <div class="col-12 col-sm-6 col-lg-4">

                <div class="loan-preview-item loan-preview-item--amount">

                    <span class="loan-preview-item__icon">
                        <i class="bi bi-cash-stack"></i>
                    </span>

                    <div class="loan-preview-item__content">

                        <span class="loan-preview-item__label">
                            مبلغ هر قسط
                        </span>

                        <strong id="preview-installment">
                            -
                        </strong>

                    </div>

                </div>

            </div>


            {{-- برنامه اقساط --}}
            <div class="col-12 col-sm-6 col-lg-4">

                <div class="loan-preview-schedule">

                    <button
                        type="button"
                        class="btn loan-preview-schedule__button w-100"
                        id="show-schedule">

                        <i class="bi bi-list-ul"></i>

                        <span>
                            مشاهده برنامه اقساط
                        </span>

                        <i class="bi bi-arrow-left"></i>

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

