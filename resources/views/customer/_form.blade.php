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

    <x-inputs.iban-input
        name="iban"
        label="شماره شبا"
        :value="old('iban', $customer->iban ?? '')"
        required
        col="col-md-6"
    />

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

{{-- حساب پس‌انداز اولیه --}}
@if (!isset($customer->id) || !$customer->exists)

    <hr class="my-4">

    <h5 class="fw-bold mb-3">
        تعریف حساب پس‌انداز
    </h5>

    {{-- نوع حساب --}}
    <div class="mb-3">

        <label for="account_type" class="form-label">
            نوع حساب
        </label>

        <select
            name="account_type"
            id="account_type"
            class="form-select @error('account_type') is-invalid @enderror"
            required
        >

            <option value="">انتخاب کنید</option>

            <option value="1"
                    data-prefix="6111"
                    @selected(old('account_type') == 1)>
            پس‌انداز
            </option>

            <option value="2"
                    data-prefix="6112"
                    @selected(old('account_type') == 2)>
            جاری
            </option>

        </select>

        @error('account_type')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror

    </div>


    {{-- شماره حساب --}}
    <div class="mb-3">

        <label for="account_number_suffix" class="form-label">
            شماره حساب
        </label>

        <div class="account-number-box">

            <span
                id="account-prefix"
                class="account-prefix"
            >
                6111-
            </span>

            <input
                type="text"
                id="account_number_suffix"
                name="account_number_suffix"
                value="{{ old('account_number_suffix') }}"
                class="account-suffix"
                maxlength="16"
                inputmode="numeric"
                autocomplete="off"
                required
            >

        </div>

        <input
            type="hidden"
            name="account_number"
            id="account_number"
            value="{{ old('account_number') }}"
        >

        @error('account_number')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror

        @error('account_number_suffix')
        <div class="text-danger small mt-1">
            {{ $message }}
        </div>
        @enderror

    </div>


    {{-- موجودی اولیه --}}
    <div class="mb-3">

        <label for="initial_balance" class="form-label">
            موجودی اولیه حساب
        </label>

        <div class="input-group">

            <input
                type="text"
                name="initial_balance"
                id="initial_balance"
                value="{{ old('initial_balance', 0) }}"
                class="form-control money-input @error('initial_balance') is-invalid @enderror"
                inputmode="numeric"
                autocomplete="off"
                required
            >

            <span class="input-group-text">
                ریال
            </span>

        </div>

        @error('initial_balance')
        <div class="invalid-feedback d-block">
            {{ $message }}
        </div>
        @enderror

    </div>

@endif
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
