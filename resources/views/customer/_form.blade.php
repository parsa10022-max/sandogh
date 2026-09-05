{{-- =========================================================
     CUSTOMER INFORMATION
========================================================= --}}

<div class="customer-form-section">

    <div class="customer-form-section__header">

        <div class="customer-form-section__icon">
            <i class="bi bi-person-vcard"></i>
        </div>

        <div>
            <h3 class="customer-form-section__title">
                اطلاعات مشتری
            </h3>

            <p class="customer-form-section__description">
                اطلاعات هویتی و تماس مشتری را وارد کنید.
            </p>
        </div>

    </div>


    {{-- =====================================================
         TWO COLUMN FORM
    ====================================================== --}}

    <div class="customer-form-grid customer-form-grid--two">

        {{-- کد مشتری --}}
        <div class="customer-form-field">

            <x-inputs.text-input
                name="customer_code"
                label="کد مشتری"
                :value="old('customer_code', $customer->customer_code ?? '')"
                required
            />

        </div>


        {{-- نام --}}
        <div class="customer-form-field">

            <x-inputs.text-input
                name="first_name"
                label="نام"
                :value="old('first_name', $customer->first_name ?? '')"
                required
            />

        </div>


        {{-- نام خانوادگی --}}
        <div class="customer-form-field">

            <x-inputs.text-input
                name="last_name"
                label="نام خانوادگی"
                :value="old('last_name', $customer->last_name ?? '')"
                required
            />

        </div>


        {{-- نام پدر --}}
        <div class="customer-form-field">

            <x-inputs.text-input
                name="father_name"
                label="نام پدر"
                :value="old('father_name', $customer->father_name ?? '')"
            />

        </div>


        {{-- کد ملی --}}
        <div class="customer-form-field">

            <x-inputs.national-code-input
                name="national_code"
                label="کد ملی"
                :value="old('national_code', $customer->national_code ?? '')"
                required
            />

        </div>


        {{-- موبایل --}}
        <div class="customer-form-field">

            <x-inputs.mobile-input
                name="mobile"
                label="شماره موبایل"
                :value="old('mobile', $customer->mobile ?? '')"
                required
            />

        </div>


        {{-- موبایل دوم --}}
        <div class="customer-form-field">

            <x-inputs.mobile-input
                name="mobile_second"
                label="شماره موبایل دوم"
                :value="old('mobile_second', $customer->mobile_second ?? '')"
            />

        </div>


        {{-- شماره شبا --}}
        <div class="customer-form-field">

            <x-inputs.iban-input
                name="iban"
                label="شماره شبا"
                :value="old('iban', $customer->iban ?? '')"
                required
            />

        </div>

    </div>

</div>


{{-- =========================================================
     INITIAL ACCOUNT
========================================================= --}}

@if (!isset($customer->id) || !$customer->exists)

    <div class="customer-form-section customer-form-section--account">

        <div class="customer-form-section__header">

            <div class="customer-form-section__icon">
                <i class="bi bi-wallet2"></i>
            </div>

            <div>

                <h3 class="customer-form-section__title">
                    حساب اولیه
                </h3>

                <p class="customer-form-section__description">
                    اطلاعات حساب مالی اولیه مشتری را وارد کنید.
                </p>

            </div>

        </div>


        <div class="customer-form-grid customer-form-grid--two">

            {{-- نوع حساب --}}
            <div class="customer-form-field">

                <label
                    for="account_type"
                    class="customer-form-label"
                >
                    نوع حساب
                    <span class="text-danger">*</span>
                </label>

                <select
                    name="account_type"
                    id="account_type"
                    class="form-select customer-form-control @error('account_type') is-invalid @enderror"
                    required
                >

                    <option value="">
                        انتخاب نوع حساب
                    </option>

                    <option
                        value="1"
                        data-prefix="6111"
                        @selected(old('account_type') == 1)
                    >
                    پس‌انداز
                    </option>

                    <option
                        value="2"
                        data-prefix="6112"
                        @selected(old('account_type') == 2)
                    >
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
            <div class="customer-form-field">

                <label
                    for="account_number_suffix"
                    class="customer-form-label"
                >
                    شماره حساب
                    <span class="text-danger">*</span>
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

                <div class="customer-form-error">
                    {{ $message }}
                </div>

                @enderror

                @error('account_number_suffix')

                <div class="customer-form-error">
                    {{ $message }}
                </div>

                @enderror

            </div>


            {{-- موجودی اولیه --}}
            <div class="customer-form-field">

                <label
                    for="initial_balance"
                    class="customer-form-label"
                >
                    موجودی اولیه حساب
                </label>

                <div class="customer-money-field">

                    <input
                        type="text"
                        name="initial_balance"
                        id="initial_balance"
                        value="{{ old('initial_balance', 0) }}"
                        class="form-control customer-form-control money-input @error('initial_balance') is-invalid @enderror"
                        inputmode="numeric"
                        autocomplete="off"
                        required
                    >

                    <span class="customer-money-field__unit">
                        ریال
                    </span>

                </div>

                @error('initial_balance')

                <div class="customer-form-error">
                    {{ $message }}
                </div>

                @enderror

            </div>

        </div>

    </div>

@endif


