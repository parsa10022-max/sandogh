@php
    /*
    |--------------------------------------------------------------------------
    | ضامن‌های وام جاری
    |--------------------------------------------------------------------------
    */

    $currentGuarantors = isset($loan) && $loan
        ? ($loan->guarantors ?? collect())
        : collect();


    /*
    |--------------------------------------------------------------------------
    | ضامن‌های آخرین وام تسویه‌شده
    |--------------------------------------------------------------------------
    */

    $previousLoanGuarantors = $previousLoanGuarantors ?? collect();


    /*
    |--------------------------------------------------------------------------
    | انتخاب منبع اطلاعات ضامن
    |--------------------------------------------------------------------------
    */

    $guarantors = $currentGuarantors->isNotEmpty()
        ? $currentGuarantors
        : $previousLoanGuarantors;


    /*
    |--------------------------------------------------------------------------
    | ضامن اول
    |--------------------------------------------------------------------------
    */

    $guarantor1 = $guarantors
        ->where('guarantor_order', 1)
        ->first();


    /*
    |--------------------------------------------------------------------------
    | ضامن دوم
    |--------------------------------------------------------------------------
    */

    $guarantor2 = $guarantors
        ->where('guarantor_order', 2)
        ->first();


    /*
    |--------------------------------------------------------------------------
    | نوع ضامن دوم
    |--------------------------------------------------------------------------
    */

    $guarantor2Type = old(
        'guarantor2_type',
        $guarantor2?->guarantor_type instanceof \App\Enums\GuarantorType
            ? $guarantor2->guarantor_type->value
            : $guarantor2?->guarantor_type
    );


    /*
    |--------------------------------------------------------------------------
    | نوع ضمانت ضامن اول
    |--------------------------------------------------------------------------
    */

    $guarantor1GuaranteeType = old(
        'guarantor1_guarantee_type',
        $guarantor1?->guarantee_type instanceof \App\Enums\GuaranteeType
            ? $guarantor1->guarantee_type->value
            : $guarantor1?->guarantee_type
    );


    /*
    |--------------------------------------------------------------------------
    | نوع ضمانت ضامن دوم
    |--------------------------------------------------------------------------
    */

    $guarantor2GuaranteeType = old(
        'guarantor2_guarantee_type',
        $guarantor2?->guarantee_type instanceof \App\Enums\GuaranteeType
            ? $guarantor2->guarantee_type->value
            : $guarantor2?->guarantee_type
    );
@endphp


<div class="loan-guarantor-form">

    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="loan-guarantor-form-header">

        <div class="loan-guarantor-form-title">

            <span class="loan-guarantor-form-title-icon">
                <i class="bi bi-people-fill"></i>
            </span>

            <div>
                <h6>اطلاعات ضامن‌ها</h6>

                <small>
                    اطلاعات ضامنین و نوع مدرک ضمانت را وارد کنید
                </small>
            </div>

        </div>

        <span class="loan-guarantor-form-badge">
            حداکثر ۲ ضامن
        </span>

    </div>


    {{-- =====================================================
         GUARANTORS
    ====================================================== --}}

    <div class="loan-guarantor-form-body">

        <div class="row g-3">

            {{-- =================================================
                 ضامن اول
            ================================================== --}}

            <div class="col-12 col-lg-6">

                <div class="loan-guarantor-box">

                    <div class="loan-guarantor-box-header">

                        <div class="loan-guarantor-box-title">

                            <span class="loan-guarantor-box-icon primary">
                                <i class="bi bi-person-badge"></i>
                            </span>

                            <div>
                                <strong>ضامن اول</strong>

                                <small>عضو صندوق</small>
                            </div>

                        </div>

                        <span class="loan-guarantor-number">
                            ۱
                        </span>

                    </div>


                    <div class="loan-guarantor-box-body">

                        <input
                            type="hidden"
                            name="guarantor1_type"
                            value="customer"
                        >


                        {{-- Customer Picker --}}

                        <div class="loan-guarantor-field">

                            @include('customer._picker', [
                                'name' => 'guarantor1_customer_id',
                                'label' => 'کد مشتری',
                                'required' => true,
                                'value' => old(
                                    'guarantor1_customer_id',
                                    $guarantor1?->customer_id
                                ),
                            ])

                        </div>


                        {{-- Guarantee Type --}}

                        <div class="loan-guarantor-field">

                            <x-inputs.select-input
                                name="guarantor1_guarantee_type"
                                label="نوع مدرک ضمانت"
                                :options="\App\Enums\GuaranteeType::options()"
                                :value="$guarantor1GuaranteeType"
                                required
                            />

                        </div>


                        {{-- اطلاعات مدرک ضمانت ضامن اول --}}

                        <div
                            id="guarantor1-guarantee-fields"
                            class="loan-guarantor-guarantee-fields
                                {{ in_array($guarantor1GuaranteeType, ['check', 'promissory_note'], true) ? '' : 'd-none' }}"
                        >

                            <div class="row g-3">

                                {{-- مبلغ ضمانت --}}

                                <div class="col-12 col-md-6">

                                    <div class="loan-guarantor-field">

                                        <label
                                            for="guarantor1_guarantee_amount"
                                            class="form-label fw-semibold"
                                        >
                                            مبلغ ضمانت
                                            <span class="text-danger">*</span>
                                        </label>

                                        <div class="input-group">

                                            <input
                                                type="text"
                                                id="guarantor1_guarantee_amount"
                                                name="guarantor1_guarantee_amount"
                                                class="form-control money-input"
                                                inputmode="numeric"
                                                autocomplete="off"
                                                value="{{ old(
                                                    'guarantor1_guarantee_amount',
                                                    $guarantor1?->guarantee_amount
                                                ) }}"
                                            >

                                            <span class="input-group-text">
                                                ریال
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                {{-- سریال --}}

                                <div class="col-12 col-md-6">

                                    <div
                                        id="guarantor1-guarantee-number"
                                        class="{{ in_array($guarantor1GuaranteeType, ['check', 'promissory_note'], true) ? '' : 'd-none' }}"
                                    >

                                        <x-inputs.text-input
                                            name="guarantor1_guarantee_number"
                                            :label="$guarantor1GuaranteeType === 'check'
                                                ? 'سریال چک'
                                                : 'سریال سفته'"
                                            :value="old(
                                                'guarantor1_guarantee_number',
                                                $guarantor1?->guarantee_number
                                            )"
                                        />

                                    </div>

                                </div>


                                {{-- شماره حساب چک ضامن اول --}}

                                <div
                                    id="guarantor1-guarantee-account"
                                    class="col-12
                                        {{ $guarantor1GuaranteeType === 'check' ? '' : 'd-none' }}"
                                >

                                    <x-inputs.text-input
                                        name="guarantor1_guarantee_account_number"
                                        label="شماره حساب"
                                        :value="old(
                                            'guarantor1_guarantee_account_number',
                                            $guarantor1?->guarantee_account_number
                                        )"
                                    />

                                </div>

                            </div>

                        </div>


                        <div class="loan-guarantor-hint">

                            <i class="bi bi-info-circle"></i>

                            ضامن اول باید از اعضای صندوق باشد.

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 ضامن دوم
            ================================================== --}}

            <div class="col-12 col-lg-6">

                <div class="loan-guarantor-box">

                    <div class="loan-guarantor-box-header">

                        <div class="loan-guarantor-box-title">

                            <span class="loan-guarantor-box-icon success">
                                <i class="bi bi-person-check"></i>
                            </span>

                            <div>
                                <strong>ضامن دوم</strong>

                                <small>انتخاب نوع ضامن</small>
                            </div>

                        </div>

                        <span class="loan-guarantor-number">
                            ۲
                        </span>

                    </div>


                    <div class="loan-guarantor-box-body">

                        {{-- نوع ضامن --}}

                        <div class="loan-guarantor-field">

                            <x-inputs.select-input
                                name="guarantor2_type"
                                label="نوع ضامن"
                                :options="\App\Enums\GuarantorType::options()"
                                :value="$guarantor2Type"
                                required
                            />

                        </div>


                        {{-- عضو صندوق --}}

                        <div
                            id="guarantor2-customer"
                            class="loan-guarantor-dynamic
                                {{ $guarantor2Type === 'customer' ? '' : 'd-none' }}"
                        >

                            @include('customer._picker', [
                                'name' => 'guarantor2_customer_id',
                                'label' => 'کد مشتری',
                                'required' => false,
                                'value' => old(
                                    'guarantor2_customer_id',
                                    $guarantor2?->customer_id
                                ),
                            ])

                        </div>


                        {{-- خود وام گیرنده --}}

                        <div
                            id="guarantor2-borrower"
                            class="loan-guarantor-dynamic
                                {{ $guarantor2Type === 'borrower' ? '' : 'd-none' }}"
                        >

                            <div class="loan-guarantor-info-box">

                                <span class="loan-guarantor-info-icon">
                                    <i class="bi bi-person-check"></i>
                                </span>

                                <div>

                                    <strong>
                                        وام‌گیرنده به عنوان ضامن
                                    </strong>

                                    <small>
                                        ضامن دوم همان وام‌گیرنده این وام است.
                                    </small>

                                </div>

                            </div>

                        </div>


                        {{-- خارج از صندوق --}}

                        <div
                            id="guarantor2-external"
                            class="loan-guarantor-dynamic
                                {{ $guarantor2Type === 'external' ? '' : 'd-none' }}"
                        >

                            <div class="row g-2">

                                <div class="col-12 col-md-6">

                                    <x-inputs.text-input
                                        name="guarantor2_first_name"
                                        label="نام"
                                        :value="old(
                                            'guarantor2_first_name',
                                            $guarantor2?->first_name
                                        )"
                                    />

                                </div>


                                <div class="col-12 col-md-6">

                                    <x-inputs.text-input
                                        name="guarantor2_last_name"
                                        label="نام خانوادگی"
                                        :value="old(
                                            'guarantor2_last_name',
                                            $guarantor2?->last_name
                                        )"
                                    />

                                </div>


                                <div class="col-12 col-md-6">

                                    <x-inputs.text-input
                                        name="guarantor2_national_code"
                                        label="کد ملی"
                                        :value="old(
                                            'guarantor2_national_code',
                                            $guarantor2?->national_code
                                        )"
                                    />

                                </div>


                                <div class="col-12 col-md-6">

                                    <x-inputs.text-input
                                        name="guarantor2_mobile"
                                        label="موبایل"
                                        :value="old(
                                            'guarantor2_mobile',
                                            $guarantor2?->mobile
                                        )"
                                    />

                                </div>

                            </div>

                        </div>


                        {{-- نوع مدرک ضمانت --}}

                        <div class="loan-guarantor-field">

                            <x-inputs.select-input
                                name="guarantor2_guarantee_type"
                                label="نوع مدرک ضمانت"
                                :options="\App\Enums\GuaranteeType::options()"
                                :value="$guarantor2GuaranteeType"
                                required
                            />

                        </div>


                        {{-- اطلاعات مدرک ضمانت ضامن دوم --}}

                        <div
                            id="guarantor2-guarantee-fields"
                            class="loan-guarantor-guarantee-fields
                                {{ in_array($guarantor2GuaranteeType, ['check', 'promissory_note'], true) ? '' : 'd-none' }}"
                        >

                            <div class="row g-3">

                                {{-- مبلغ ضمانت --}}

                                <div class="col-12 col-md-6">

                                    <div class="loan-guarantor-field">

                                        <label
                                            for="guarantor2_guarantee_amount"
                                            class="form-label fw-semibold"
                                        >
                                            مبلغ ضمانت
                                            <span class="text-danger">*</span>
                                        </label>

                                        <div class="input-group">

                                            <input
                                                type="text"
                                                id="guarantor2_guarantee_amount"
                                                name="guarantor2_guarantee_amount"
                                                class="form-control money-input"
                                                inputmode="numeric"
                                                autocomplete="off"
                                                value="{{ old(
                                                    'guarantor2_guarantee_amount',
                                                    $guarantor2?->guarantee_amount
                                                ) }}"
                                            >

                                            <span class="input-group-text">
                                                ریال
                                            </span>

                                        </div>

                                    </div>

                                </div>


                                {{-- سریال --}}

                                <div class="col-12 col-md-6">

                                    <div
                                        id="guarantor2-guarantee-number"
                                        class="{{ in_array($guarantor2GuaranteeType, ['check', 'promissory_note'], true) ? '' : 'd-none' }}"
                                    >

                                        <x-inputs.text-input
                                            name="guarantor2_guarantee_number"
                                            :label="$guarantor2GuaranteeType === 'check'
                                                ? 'سریال چک'
                                                : 'سریال سفته'"
                                            :value="old(
                                                'guarantor2_guarantee_number',
                                                $guarantor2?->guarantee_number
                                            )"
                                        />

                                    </div>

                                </div>


                                {{-- شماره حساب چک ضامن دوم --}}

                                <div
                                    id="guarantor2-guarantee-account"
                                    class="col-12
                                        {{ $guarantor2GuaranteeType === 'check' ? '' : 'd-none' }}"
                                >

                                    <x-inputs.text-input
                                        name="guarantor2_guarantee_account_number"
                                        label="شماره حساب"
                                        :value="old(
                                            'guarantor2_guarantee_account_number',
                                            $guarantor2?->guarantee_account_number
                                        )"
                                    />

                                </div>

                            </div>

                        </div>


                        <div class="loan-guarantor-hint">

                            <i class="bi bi-info-circle"></i>

                            می‌توانید عضو صندوق، وام‌گیرنده یا شخص خارج از صندوق را انتخاب کنید.

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
