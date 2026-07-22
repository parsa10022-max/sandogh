{{-- ============================= --}}
{{-- اطلاعات وام --}}
{{-- ============================= --}}

<div class="row">

    <div class="col-md-6 mb-3">

        <x-inputs.select-input
            name="customer_id"
            label="مشتری"
            :options="$customers->pluck('display_name','id')->toArray()"
            :value="old('customer_id', $loan->customer_id ?? '')"
            required
        />

    </div>

    <div class="col-md-6 mb-3">

        <x-inputs.select-input
            name="loan_type_id"
            label="نوع وام"
            :options="$loanTypes->pluck('name','id')->toArray()"
            :attributesMap="$loanTypes
                ->mapWithKeys(fn($item)=>[
                    $item->id=>[
                        'data-prefix'=>$item->prefix
                    ]
                ])
                ->toArray()"
            :value="old('loan_type_id', $loan->loan_type_id ?? '')"
            required
        />

    </div>

</div>

<div class="row">

    <div class="row">

        <div class="col-md-6 mb-3">

            <label class="form-label">
                شماره وام
            </label>

            <div class="d-flex loan-number-box">

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
                    required>

            </div>

            <small class="text-muted">
                پیش شماره بر اساس نوع وام به صورت خودکار نمایش داده می‌شود.
            </small>

        </div>

    </div>

</div>

<div class="row">

    <div class="col-md-4 mb-3">

        <x-inputs.money-input
            name="loan_amount"
            label="مبلغ وام"
            :value="old('loan_amount', $loan->loan_amount ?? '')"
            required
        />

    </div>

    <div class="col-md-4 mb-3">

        <x-inputs.text-input
            name="installment_count"
            label="تعداد اقساط"
            :value="old('installment_count', $loan->installment_count ?? '')"
            required
        />

    </div>

    <div class="col-md-4 mb-3">

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

</div>

{{-- ============================= --}}
{{-- تاریخ ها --}}
{{-- ============================= --}}

<div class="row">

    <div class="col-md-4 mb-3">

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

{{-- ============================= --}}
{{-- پیش نمایش --}}
{{-- ============================= --}}

<div
    id="loan-preview-card"
    class="card border-success shadow-sm mt-4 d-none">

    <div class="card-header bg-success text-white">

        <i class="bi bi-calculator me-2"></i>

        نتیجه محاسبه وام

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3 mb-3">

                <small class="text-muted">

                    تاریخ ثبت

                </small>

                <div
                    id="preview-start-date"
                    class="fw-bold">

                    -

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <small class="text-muted">

                    اولین سررسید

                </small>

                <div
                    id="preview-first-date"
                    class="fw-bold">

                    -

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <small class="text-muted">

                    آخرین سررسید

                </small>

                <div
                    id="preview-last-date"
                    class="fw-bold">

                    -

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <small class="text-muted">

                    مبلغ هر قسط

                </small>

                <div
                    id="preview-installment"
                    class="fw-bold text-success">

                    -

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <small class="text-muted">

                    تعداد اقساط

                </small>

                <div
                    id="preview-count"
                    class="fw-bold">

                    -

                </div>

            </div>

            <div class="col-md-3 d-flex align-items-end">

                <button
                    type="button"
                    id="show-schedule"
                    class="btn btn-outline-primary w-100"
                    disabled>

                    <i class="bi bi-list-ul me-1"></i>

                    مشاهده برنامه اقساط

                </button>

            </div>

        </div>

    </div>

</div>

{{-- ============================= --}}
{{-- توضیحات --}}
{{-- ============================= --}}

<div class="row mt-4">

    <div class="col-md-12">

        <x-inputs.textarea-input
            name="description"
            label="توضیحات"
            :value="old('description', $loan->description ?? '')"
        />

    </div>

</div>



<input
    type="hidden"
    id="server-date"
    value="{{ now()->toDateString() }}">

{{-- ============================= --}}
{{-- دکمه ها --}}
{{-- ============================= --}}

<div class="d-flex justify-content-between align-items-center mt-4">

    <div>

        <button
            type="submit"
            class="btn btn-success">

            <i class="bi bi-check-circle me-1"></i>

            ذخیره وام

        </button>

        <a
            href="{{ route('loans.index') }}"
            class="btn btn-secondary">

            <i class="bi bi-x-circle me-1"></i>

            انصراف

        </a>

    </div>

</div>
