<div class="row">

    <div class="col-md-4 mb-3">

        <x-inputs.text-input
            name="customer_code"
            label="کد مشتری"
            :value="old('customer_code', $customer->customer_code ?? '')"
            required
        />

    </div>

    <div class="col-md-4 mb-3">

        <x-inputs.text-input
            name="first_name"
            label="نام"
            :value="old('first_name', $customer->first_name ?? '')"
            required
        />

    </div>

    <div class="col-md-4 mb-3">

        <x-inputs.text-input
            name="last_name"
            label="نام خانوادگی"
            :value="old('last_name', $customer->last_name ?? '')"
            required
        />

    </div>

</div>

<div class="row">

    <div class="col-md-4 mb-3">

        <x-inputs.text-input
            name="father_name"
            label="نام پدر"
            :value="old('father_name', $customer->father_name ?? '')"
        />

    </div>

    <div class="col-md-4 mb-3">

        <x-inputs.national-code-input
            name="national_code"
            label="کد ملی"
            :value="old('national_code', $customer->national_code ?? '')"
            required
        />

    </div>

    <div class="col-md-4 mb-3">

        <x-inputs.mobile-input
            name="mobile"
            label="شماره موبایل"
            :value="old('mobile', $customer->mobile ?? '')"
            required
        />

    </div>
    <div class="col-md-4 mb-3">

        <x-inputs.mobile-input
            name="mobile_second"
            label="شماره موبایل2"
            :value="old('mobile', $customer->mobile ?? '')"
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

    <a href="{{ route('customers.index') }}"
       class="btn btn-secondary">

        انصراف

    </a>

</div>
