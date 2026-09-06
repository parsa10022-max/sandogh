{{-- اطلاعات اصلی --}}
<div class="loan-type-form-section">

    <div class="loan-type-form-section-header">

        <div class="loan-type-form-icon">
            <i class="bi bi-info-circle"></i>
        </div>

        <div>
            <h6>اطلاعات نوع وام</h6>
            <span>اطلاعات اصلی نوع وام را وارد کنید.</span>
        </div>

    </div>


    <div class="row g-3">

        {{-- نام نوع وام --}}
        <div class="col-12 col-md-6">

            <x-inputs.text-input
                name="name"
                label="نام نوع وام"
                :value="old('name', $loanType->name ?? '')"
                required
            />

        </div>


        {{-- پیشوند --}}
        <div class="col-12 col-md-6">

            <x-inputs.text-input
                name="prefix"
                label="پیشوند"
                :value="old('prefix', $loanType->prefix ?? '')"
                required
            />

        </div>


        {{-- توضیحات --}}
        <div class="col-12">

            <x-inputs.textarea-input
                name="description"
                label="توضیحات"
                :value="old('description', $loanType->description ?? '')"
            />

        </div>


        {{-- وضعیت --}}
        <div class="col-12 col-md-6">

            <x-inputs.select-input
                name="status"
                label="وضعیت"
                :options="\App\Enums\LoanTypeStatus::options()"
                :value="old(
                    'status',
                    isset($loanType)
                        ? $loanType->status->value
                        : \App\Enums\LoanTypeStatus::ACTIVE->value
                )"
                required
            />

        </div>

    </div>

</div>


{{-- دکمه‌ها --}}
<div class="loan-type-form-actions">

    <button
        type="submit"
        class="btn btn-primary loan-type-save-btn"
    >
        <i class="bi bi-check-lg"></i>
        <span>ذخیره</span>
    </button>


    <a
        href="{{ route('loan-types.index') }}"
        class="btn loan-type-cancel-btn"
    >
        <i class="bi bi-x-lg"></i>
        <span>انصراف</span>
    </a>

</div>

