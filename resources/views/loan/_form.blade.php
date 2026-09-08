{{-- ========================================================================= --}}
{{-- اطلاعات اصلی وام --}}
{{-- ========================================================================= --}}

<div class="loan-form-section">

    <div class="loan-form-section__header">
        <div class="loan-form-section__icon">
            <i class="bi bi-cash-coin"></i>
        </div>

        <div>
            <h3 class="loan-form-section__title">
                اطلاعات وام
            </h3>

            <p class="loan-form-section__subtitle">
                اطلاعات اصلی وام را وارد کنید.
            </p>
        </div>
    </div>


    <div class="row g-3">

        {{-- وام گیرنده --}}
        <div class="col-12 col-lg-6">

            @include('customer._picker', [
                'name' => 'customer_id',
                'label' => 'وام گیرنده',
                'required' => true,
                'value' => old(
                    'customer_id',
                    $loanRequest?->customer_id ?? $loan->customer_id ?? ''
                )
            ])

        </div>


        {{-- نوع وام --}}
        <div class="col-12 col-lg-6">

            <x-inputs.select-input
                name="loan_type_id"
                label="نوع وام"
                :options="$loanTypes->pluck('name', 'id')->toArray()"
                :attributesMap="$loanTypes
                    ->mapWithKeys(fn($item) => [
                        $item->id => [
                            'data-prefix' => $item->prefix
                        ]
                    ])
                    ->toArray()"
                :value="old(
                    'loan_type_id',
                    $loan->loan_type_id ?? ''
                )"
                required
            />

        </div>


        {{-- شماره وام --}}
        <div class="col-12 col-lg-6">

            <label
                for="loan_number"
                class="form-label">
                شماره وام
            </label>

            <div class="loan-number-box">

                <span
                    id="loan-prefix"
                    class="loan-prefix">

                    {{ $loan->loanType?->prefix ?? '----' }}

                </span>

                <input
                    type="text"
                    id="loan_number"
                    name="loan_number"
                    class="form-control"
                    value="{{ old('loan_number', $loan->loan_number ?? '') }}"
                    required
                >

            </div>

            <div class="loan-form-help">
                <i class="bi bi-info-circle"></i>
                پیش‌شماره بر اساس نوع وام به‌صورت خودکار نمایش داده می‌شود.
            </div>

        </div>


        {{-- مبلغ وام --}}
        <div class="col-12 col-lg-6">

            <label
                for="loan_amount"
                class="form-label">
                مبلغ وام
            </label>

            <div class="input-group">

                <input
                    type="text"
                    inputmode="numeric"
                    id="loan_amount"
                    name="loan_amount"
                    class="form-control money-input"
                    value="{{ old(
        'loan_amount',
        $loanRequest?->approved_amount ?? $loan->loan_amount ?? ''
    ) }}"
                    autocomplete="off"
                    required
                >

                <span class="input-group-text">
                    ریال
                </span>

            </div>

        </div>


        {{-- تعداد اقساط --}}
        <div class="col-12 col-md-6 col-lg-4">

            <x-inputs.text-input
                name="installment_count"
                label="تعداد اقساط"
                :value="old(
                    'installment_count',
                    $loan->installment_count ?? ''
                )"
                required
            />

        </div>


        {{-- دوره پرداخت --}}
        <div class="col-12 col-md-6 col-lg-4">

            <x-inputs.select-input
                name="installment_interval"
                label="دوره پرداخت اقساط"
                :options="\App\Enums\InstallmentInterval::options()"
                :value="old(
                    'installment_interval',
                    $loan->installment_interval?->value
                )"
                required
            />

        </div>


        {{-- تاریخ ثبت --}}
        <div class="col-12 col-md-6 col-lg-4">

            <x-inputs.date-input
                name="start_date"
                label="تاریخ ثبت وام"
                :value="old(
                    'start_date',
                    $loan->exists
                        ? $loan->start_date_jalali
                        : app(\App\Services\Date\JalaliDateService::class)->today()
                )"
                required
            />

        </div>

    </div>

</div>


{{-- ========================================================================= --}}
{{-- ضامن‌ها --}}
{{-- ========================================================================= --}}

<div class="loan-form-section loan-guarantors-section">

    <div class="loan-form-section__header">

        <div class="loan-form-section__icon loan-form-section__icon--purple">
            <i class="bi bi-people"></i>
        </div>

        <div>
            <h3 class="loan-form-section__title">
                ضامن‌های وام
            </h3>

            <p class="loan-form-section__subtitle">
                اطلاعات ضامن اول و دوم را وارد کنید.
            </p>
        </div>

    </div>


    <div class="loan-form-section__content">

        @include('loan._guarantors', [
            'previousLoanGuarantors' => $previousLoanGuarantors ?? collect(),
        ])

    </div>


</div>


{{-- ========================================================================= --}}
{{-- پیش‌نمایش محاسبه وام --}}
{{-- ========================================================================= --}}

<div
    id="loan-preview-card"
    class="loan-preview-card d-none">

    <div class="loan-preview-card__header">

        <div class="loan-preview-card__icon">
            <i class="bi bi-calculator"></i>
        </div>

        <div>
            <h3 class="loan-preview-card__title">
                نتیجه محاسبه وام
            </h3>

            <p class="loan-preview-card__subtitle">
                اطلاعات محاسبه‌شده وام
            </p>
        </div>

    </div>


    <div class="loan-preview-card__body">

        <div class="row g-3">

            {{-- تاریخ ثبت --}}
            <div class="col-6 col-md-4 col-lg-3">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__label">
                        تاریخ ثبت
                    </span>

                    <strong
                        id="preview-start-date"
                        class="loan-preview-item__value">

                        -

                    </strong>

                </div>

            </div>


            {{-- اولین سررسید --}}
            <div class="col-6 col-md-4 col-lg-3">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__label">
                        اولین سررسید
                    </span>

                    <strong
                        id="preview-first-date"
                        class="loan-preview-item__value">

                        -

                    </strong>

                </div>

            </div>


            {{-- آخرین سررسید --}}
            <div class="col-6 col-md-4 col-lg-3">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__label">
                        آخرین سررسید
                    </span>

                    <strong
                        id="preview-last-date"
                        class="loan-preview-item__value">

                        -

                    </strong>

                </div>

            </div>


            {{-- مبلغ قسط --}}
            <div class="col-6 col-md-4 col-lg-3">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__label">
                        مبلغ هر قسط
                    </span>

                    <strong
                        id="preview-installment"
                        class="loan-preview-item__value loan-preview-item__value--success">

                        -

                    </strong>

                </div>

            </div>


            {{-- تعداد اقساط --}}
            <div class="col-6 col-md-4 col-lg-3">

                <div class="loan-preview-item">

                    <span class="loan-preview-item__label">
                        تعداد اقساط
                    </span>

                    <strong
                        id="preview-count"
                        class="loan-preview-item__value">

                        -

                    </strong>

                </div>

            </div>


            {{-- مشاهده برنامه --}}
            <div class="col-12 col-md-8 col-lg-3 d-flex">

                <button
                    type="button"
                    id="show-schedule"
                    class="btn btn-outline-primary w-100 align-self-end"
                    disabled>

                    <i class="bi bi-list-ul me-1"></i>

                    مشاهده برنامه اقساط

                </button>

            </div>

        </div>

    </div>

</div>

{{-- =========================================================
     LOAN INSTALLMENT SCHEDULE
========================================================= --}}



{{-- ========================================================================= --}}
{{-- توضیحات --}}
{{-- ========================================================================= --}}

<div class="loan-form-section">

    <div class="loan-form-section__header">

        <div class="loan-form-section__icon loan-form-section__icon--blue">
            <i class="bi bi-chat-left-text"></i>
        </div>

        <div>
            <h3 class="loan-form-section__title">
                توضیحات
            </h3>

            <p class="loan-form-section__subtitle">
                در صورت نیاز توضیحات مربوط به وام را وارد کنید.
            </p>
        </div>

    </div>


    <div class="loan-form-section__content">

        <x-inputs.textarea-input
            name="description"
            label="توضیحات"
            :value="old(
                'description',
                $loan->description ?? ''
            )"
        />

    </div>

</div>


{{-- ========================================================================= --}}
{{-- تاریخ سرور --}}
{{-- ========================================================================= --}}

<input
    type="hidden"
    id="server-date"
    value="{{ now()->toDateString() }}"
>


{{-- ========================================================================= --}}
{{-- برنامه اقساط --}}
{{-- ========================================================================= --}}

<div
    id="loan-schedule-card"
    class="loan-schedule-card d-none">

    <div class="loan-schedule-card__header">

        <div class="loan-schedule-card__title-wrapper">

            <div class="loan-schedule-card__icon">
                <i class="bi bi-calendar-week"></i>
            </div>

            <div>

                <h3 class="loan-schedule-card__title">
                    برنامه بازپرداخت وام
                </h3>

                <p class="loan-schedule-card__subtitle">
                    جزئیات سررسید و مبلغ اقساط
                </p>

            </div>

        </div>

    </div>


    <div class="loan-schedule-card__body">

        <div class="table-responsive">

            <table class="table loan-schedule-table align-middle text-center mb-0">

                <thead>

                <tr>

                    <th>
                        قسط
                    </th>

                    <th>
                        سررسید
                    </th>

                    <th>
                        مبلغ
                    </th>

                </tr>

                </thead>

                <tbody id="loan-schedule-body">
                </tbody>

            </table>

        </div>


        <div class="loan-schedule-summary">

            <div>

                <span>
                    تعداد اقساط
                </span>

                <strong id="schedule-total-count">
                    -
                </strong>

            </div>


            <div>

                <span>
                    جمع اقساط
                </span>

                <strong
                    id="schedule-total-amount"
                    class="text-success">

                    -

                </strong>

            </div>

        </div>

    </div>

</div>


{{-- ========================================================================= --}}
{{-- دکمه‌های فرم --}}
{{-- ========================================================================= --}}

{{-- ============================= --}}
{{-- دکمه های فرم --}}
{{-- ============================= --}}

<div class="loan-form-actions">

    <button
        type="submit"
        class="loan-action-btn loan-action-primary">

        <span class="loan-action-icon">
            <i class="bi bi-check-lg"></i>
        </span>

        <span>
            ذخیره وام
        </span>

    </button>

    <a
        href="{{ route('loans.index') }}"
        class="loan-action-btn loan-action-secondary">

        <span class="loan-action-icon">
            <i class="bi bi-x-lg"></i>
        </span>

        <span>
            انصراف
        </span>

    </a>

</div>

