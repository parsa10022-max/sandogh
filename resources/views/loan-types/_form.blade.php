<div class="row">

    <div class="col-md-6 mb-3">

        <x-inputs.text-input
            name="name"
            label="نام نوع وام"
            :value="old('name', $loanType->name ?? '')"
            required
        />

    </div>

    <div class="col-md-6 mb-3">

        <x-inputs.text-input
            name="prefix"
            label="پیشوند"
            :value="old('prefix', $loanType->prefix ?? '')"
            required
        />

    </div>

</div>

<div class="row">

    <div class="col-md-12 mb-3">

        <x-inputs.textarea-input
            name="description"
            label="توضیحات"
            :value="old('description', $loanType->description ?? '')"
        />

    </div>

</div>

<div class="row">

    <div class="row">

        <x-inputs.select-input
            name="is_active"
            label="وضعیت"
            :options="\App\Enums\LoanTypeStatus::options()"
            :value="old('is_active', isset($loanType) ? $loanType->is_active->value : \App\Enums\LoanTypeStatus::ACTIVE->value)"
            required
        />
    </div>

</div>

<div class="mt-4">

    <button type="submit"
            class="btn btn-success">

        <i class="bi bi-check-circle"></i>

        ذخیره

    </button>

    <a href="{{ route('loan-types.index') }}"
       class="btn btn-secondary">

        انصراف

    </a>

</div>
