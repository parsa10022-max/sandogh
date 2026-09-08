document.addEventListener('DOMContentLoaded', () => {
    console.log('LOAN FORM DOM READY');

    const form = document.getElementById('loan-form');

    console.log('LOAN FORM ELEMENT:', form);

    if (!form) return;

    const previewCard =
        document.getElementById('loan-preview-card');


    /*
    |--------------------------------------------------------------------------
    | پیش شماره وام
    |--------------------------------------------------------------------------
    */

    const loanType =
        form.elements.loan_type_id;

    const prefix =
        document.getElementById('loan-prefix');


    function updateLoanPrefix() {

        if (!loanType || !prefix) {
            return;
        }

        const option =
            loanType.options[
                loanType.selectedIndex
            ];

        prefix.textContent =
            option?.dataset?.prefix ?? '----';
    }


    updateLoanPrefix();


    loanType?.addEventListener(
        'change',
        updateLoanPrefix
    );


    /*
    |--------------------------------------------------------------------------
    | ضامن‌های وام قبلی
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'customer:selected',
        handleCustomerSelected
    );


    async function handleCustomerSelected(event) {

        const detail =
            event.detail;

        /*
        |--------------------------------------------------------------------------
        | فقط انتخاب وام‌گیرنده برای ما مهم است
        |--------------------------------------------------------------------------
        */

        if (
            !detail ||
            detail.name !== 'customer_id'
        ) {
            return;
        }

        const customer =
            detail.customer;

        if (!customer?.id) {
            return;
        }

        console.log(
            'SELECTED LOAN CUSTOMER:',
            customer
        );


        /*
        |--------------------------------------------------------------------------
        | دریافت ضامن‌های وام قبلی
        |--------------------------------------------------------------------------
        */

        try {

            const response =
                await fetch(
                    `/loans/previous-guarantors/${customer.id}`,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest',
                        },
                    }
                );


            if (!response.ok) {

                throw new Error(
                    `HTTP ${response.status}`
                );
            }


            const result =
                await response.json();


            console.log(
                'PREVIOUS GUARANTORS:',
                result
            );


            /*
            |--------------------------------------------------------------------------
            | سابقه وام پیدا نشد
            |--------------------------------------------------------------------------
            */

            if (
                !result.found ||
                !Array.isArray(result.guarantors) ||
                result.guarantors.length === 0
            ) {

                clearPreviousGuarantors();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | پر کردن ضامن‌های قبلی
            |--------------------------------------------------------------------------
            */

            fillPreviousGuarantors(
                result.guarantors
            );

        }

        catch (error) {

            console.error(
                'PREVIOUS GUARANTORS ERROR:',
                error
            );

            clearPreviousGuarantors();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | پر کردن ضامن‌های قبلی
    |--------------------------------------------------------------------------
    */

    function fillPreviousGuarantors(
        guarantors
    ) {

        clearPreviousGuarantors();


        guarantors.forEach(
            guarantor => {

                const order =
                    Number(
                        guarantor.guarantor_order
                    );


                if (
                    order !== 1 &&
                    order !== 2
                ) {
                    return;
                }


                fillGuarantor(
                    order,
                    guarantor
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | پر کردن اطلاعات یک ضامن
    |--------------------------------------------------------------------------
    */

    function fillGuarantor(
        order,
        guarantor
    ) {

        console.log(
            'FILL GUARANTOR CALLED:',
            order,
            guarantor
        );


        /*
        |--------------------------------------------------------------------------
        | نوع ضامن
        |--------------------------------------------------------------------------
        */

        const typeSelect =
            form.elements[
                `guarantor${order}_type`
            ];


        if (typeSelect) {

            typeSelect.value =
                normalizeEnumValue(
                    guarantor.guarantor_type
                );


            typeSelect.dispatchEvent(
                new Event(
                    'change',
                    {
                        bubbles: true
                    }
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | مشتری ضامن
        |--------------------------------------------------------------------------
        */

        if (
            normalizeEnumValue(
                guarantor.guarantor_type
            ) === 'customer'
        ) {

            if (
                guarantor.customer?.id
            ) {

                setCustomerPicker(
                    `guarantor${order}_customer_id`,
                    guarantor.customer
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | نوع ضمانت
        |--------------------------------------------------------------------------
        */

        const guaranteeType =
            form.elements[
                `guarantor${order}_guarantee_type`
            ];


        if (guaranteeType) {

            guaranteeType.value =
                normalizeEnumValue(
                    guarantor.guarantee_type
                );


            /*
            |--------------------------------------------------------------------------
            | اجرای JS مربوط به نمایش فیلدهای ضمانت
            |--------------------------------------------------------------------------
            */

            guaranteeType.dispatchEvent(
                new Event(
                    'change',
                    {
                        bubbles: true
                    }
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | مبلغ ضمانت
        |--------------------------------------------------------------------------
        */

        const amount =
            form.elements[
                `guarantor${order}_guarantee_amount`
            ];


        if (amount) {

            amount.value =
                formatMoneyValue(
                    guarantor.guarantee_amount
                );


            amount.dispatchEvent(
                new Event(
                    'input',
                    {
                        bubbles: true
                    }
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | سریال / شماره ضمانت
        |--------------------------------------------------------------------------
        */

        const number =
            form.elements[
                `guarantor${order}_guarantee_number`
            ];


        if (number) {

            number.value =
                guarantor.guarantee_number ?? '';
        }


        /*
        |--------------------------------------------------------------------------
        | شماره حساب ضمانت
        |--------------------------------------------------------------------------
        */

        const accountName =
            `guarantor${order}_guarantee_account_number`;


        const account =
            form.elements[accountName];


        console.log(
            'GUARANTEE ACCOUNT FROM DATA:',
            guarantor.guarantee_account_number
        );


        console.log(
            'ACCOUNT INPUT NAME:',
            accountName
        );


        console.log(
            'ACCOUNT INPUT ELEMENT:',
            account
        );


        if (account) {

            account.value =
                guarantor.guarantee_account_number ?? '';


            console.log(
                'ACCOUNT INPUT VALUE AFTER SET:',
                account.value
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | قرار دادن مشتری داخل Customer Picker
    |--------------------------------------------------------------------------
    */

    function setCustomerPicker(
        inputName,
        customer
    ) {

        const pickers =
            document.querySelectorAll(
                '.customer-picker'
            );


        pickers.forEach(
            picker => {

                const hiddenInput =
                    picker.querySelector(
                        '.customer-id'
                    );


                if (
                    !hiddenInput ||
                    hiddenInput.name !== inputName
                ) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | شناسه مشتری
                |--------------------------------------------------------------------------
                */

                hiddenInput.value =
                    customer.id ?? '';


                /*
                |--------------------------------------------------------------------------
                | کد عضویت
                |--------------------------------------------------------------------------
                */

                const codeInput =
                    picker.querySelector(
                        '.customer-code'
                    );


                if (codeInput) {

                    codeInput.value =
                        customer.code ?? '';
                }


                /*
                |--------------------------------------------------------------------------
                | نام
                |--------------------------------------------------------------------------
                */

                const nameBox =
                    picker.querySelector(
                        '.customer-name'
                    );


                if (nameBox) {

                    nameBox.textContent =
                        customer.name ?? '';
                }


                /*
                |--------------------------------------------------------------------------
                | کد عضویت نمایشی
                |--------------------------------------------------------------------------
                */

                const codeBox =
                    picker.querySelector(
                        '.customer-code-view'
                    );


                if (codeBox) {

                    codeBox.textContent =
                        customer.code ?? '';
                }


                /*
                |--------------------------------------------------------------------------
                | موبایل
                |--------------------------------------------------------------------------
                */

                const mobileBox =
                    picker.querySelector(
                        '.customer-mobile'
                    );


                if (mobileBox) {

                    mobileBox.textContent =
                        customer.mobile ?? '';
                }


                /*
                |--------------------------------------------------------------------------
                | نمایش نتیجه
                |--------------------------------------------------------------------------
                */

                const resultBox =
                    picker.querySelector(
                        '.customer-result'
                    );


                if (resultBox) {

                    resultBox.classList.remove(
                        'd-none'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | مخفی کردن خطا
                |--------------------------------------------------------------------------
                */

                const errorBox =
                    picker.querySelector(
                        '.customer-error'
                    );


                if (errorBox) {

                    errorBox.classList.add(
                        'd-none'
                    );

                    errorBox.textContent = '';
                }
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | پاک کردن اطلاعات ضامن‌های پیشنهادی
    |--------------------------------------------------------------------------
    */

    function clearPreviousGuarantors() {

        [1, 2].forEach(
            order => {

                /*
                |--------------------------------------------------------------------------
                | نوع ضمانت
                |--------------------------------------------------------------------------
                */

                const guaranteeType =
                    form.elements[
                        `guarantor${order}_guarantee_type`
                    ];


                /*
                |--------------------------------------------------------------------------
                | مبلغ
                |--------------------------------------------------------------------------
                */

                const amount =
                    form.elements[
                        `guarantor${order}_guarantee_amount`
                    ];


                /*
                |--------------------------------------------------------------------------
                | سریال
                |--------------------------------------------------------------------------
                */

                const number =
                    form.elements[
                        `guarantor${order}_guarantee_number`
                    ];


                /*
                |--------------------------------------------------------------------------
                | شماره حساب
                |--------------------------------------------------------------------------
                */

                const account =
                    form.elements[
                        `guarantor${order}_guarantee_account_number`
                    ];


                /*
                |--------------------------------------------------------------------------
                | نوع ضمانت
                |--------------------------------------------------------------------------
                */

                if (guaranteeType) {

                    guaranteeType.value = '';

                    guaranteeType.dispatchEvent(
                        new Event(
                            'change',
                            {
                                bubbles: true
                            }
                        )
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | مبلغ
                |--------------------------------------------------------------------------
                */

                if (amount) {

                    amount.value = '';
                }


                /*
                |--------------------------------------------------------------------------
                | سریال
                |--------------------------------------------------------------------------
                */

                if (number) {

                    number.value = '';
                }


                /*
                |--------------------------------------------------------------------------
                | شماره حساب
                |--------------------------------------------------------------------------
                */

                if (account) {

                    account.value = '';
                }


                /*
                |--------------------------------------------------------------------------
                | مشتری ضامن
                |--------------------------------------------------------------------------
                */

                const customerPickers =
                    document.querySelectorAll(
                        '.customer-picker'
                    );


                customerPickers.forEach(
                    picker => {

                        const hiddenInput =
                            picker.querySelector(
                                '.customer-id'
                            );


                        if (
                            !hiddenInput ||
                            hiddenInput.name !==
                            `guarantor${order}_customer_id`
                        ) {
                            return;
                        }


                        hiddenInput.value = '';


                        const codeInput =
                            picker.querySelector(
                                '.customer-code'
                            );


                        if (codeInput) {
                            codeInput.value = '';
                        }


                        const nameBox =
                            picker.querySelector(
                                '.customer-name'
                            );


                        if (nameBox) {
                            nameBox.textContent = '';
                        }


                        const codeBox =
                            picker.querySelector(
                                '.customer-code-view'
                            );


                        if (codeBox) {
                            codeBox.textContent = '';
                        }


                        const mobileBox =
                            picker.querySelector(
                                '.customer-mobile'
                            );


                        if (mobileBox) {
                            mobileBox.textContent = '';
                        }


                        const resultBox =
                            picker.querySelector(
                                '.customer-result'
                            );


                        if (resultBox) {

                            resultBox.classList.add(
                                'd-none'
                            );
                        }
                    }
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | نرمال‌سازی مقدار Enum
    |--------------------------------------------------------------------------
    */

    function normalizeEnumValue(value) {

        if (
            value === null ||
            value === undefined
        ) {

            return '';
        }


        /*
        |--------------------------------------------------------------------------
        | اگر API به هر دلیلی object برگرداند
        |--------------------------------------------------------------------------
        */

        if (
            typeof value === 'object' &&
            value.value !== undefined
        ) {

            return value.value;
        }


        return String(value);
    }


    /*
    |--------------------------------------------------------------------------
    | فرمت مبلغ
    |--------------------------------------------------------------------------
    */

    function formatMoneyValue(value) {

        if (
            value === null ||
            value === undefined ||
            value === ''
        ) {

            return '';
        }


        const number =
            Number(
                String(value)
                    .replace(/,/g, '')
            );


        if (
            !Number.isFinite(number)
        ) {

            return '';
        }


        return number.toLocaleString(
            'en-US'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | محاسبه وام
    |--------------------------------------------------------------------------
    */

    const fields = [
        'loan_amount',
        'installment_count',
        'installment_interval',
        'start_date'
    ];


    fields.forEach(
        name => {

            const element =
                form.elements[name];


            if (!element) {
                return;
            }


            element.addEventListener(
                'change',
                calculateLoan
            );


            element.addEventListener(
                'keyup',
                calculateLoan
            );
        }
    );


    async function calculateLoan() {

        const loanAmount =
            form.elements.loan_amount.value
                .replace(/,/g, '');


        const installmentCount =
            form.elements.installment_count.value;


        const interval =
            form.elements.installment_interval.value;


        const startDate =
            form.elements.start_date.value;


        if (
            !loanAmount ||
            !installmentCount ||
            !interval ||
            !startDate
        ) {

            hidePreview();

            return;
        }


        try {

            const response =
                await fetch(
                    form.dataset.calculateUrl,
                    {

                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                )?.content ?? '',
                        },


                        body: JSON.stringify({

                            loan_amount:
                                loanAmount,

                            installment_count:
                                installmentCount,

                            installment_interval:
                                interval,

                            start_date:
                                startDate,
                        }),
                    }
                );


            if (!response.ok) {

                throw new Error(
                    `HTTP ${response.status}`
                );
            }


            const result =
                await response.json();


            if (!result.success) {

                hidePreview();

                return;
            }


            renderPreview(
                result.data
            );

        }

        catch (error) {

            console.error(
                'LOAN CALCULATION ERROR:',
                error
            );

            hidePreview();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | نمایش نتیجه محاسبه
    |--------------------------------------------------------------------------
    */

    function renderPreview(data) {

        const startDate =
            document.getElementById(
                'preview-start-date'
            );


        const firstDate =
            document.getElementById(
                'preview-first-date'
            );


        const lastDate =
            document.getElementById(
                'preview-last-date'
            );


        const count =
            document.getElementById(
                'preview-count'
            );


        const installment =
            document.getElementById(
                'preview-installment'
            );


        if (startDate) {

            startDate.textContent =
                data.start_date ?? '-';
        }


        if (firstDate) {

            firstDate.textContent =
                data.first_due_date ?? '-';
        }


        if (lastDate) {

            lastDate.textContent =
                data.last_due_date ?? '-';
        }


        if (count) {

            count.textContent =
                data.installment_count ?? '-';
        }


        if (installment) {

            installment.textContent =
                `${data.installment_amount ?? '-'} ریال`;
        }


        previewCard?.classList.remove(
            'd-none'
        );


        /*
        |--------------------------------------------------------------------------
        | دکمه برنامه اقساط
        |--------------------------------------------------------------------------
        */

        const scheduleButton =
            document.getElementById(
                'show-schedule'
            );


        if (scheduleButton) {

            scheduleButton.disabled =
                false;


            scheduleButton.dataset.schedule =
                JSON.stringify(
                    data.schedule ?? []
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | مخفی کردن کارت پیش‌نمایش
    |--------------------------------------------------------------------------
    */

    function hidePreview() {

        previewCard?.classList.add(
            'd-none'
        );


        const scheduleButton =
            document.getElementById(
                'show-schedule'
            );


        if (scheduleButton) {

            scheduleButton.disabled =
                true;


            scheduleButton.removeAttribute(
                'data-schedule'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | نمایش برنامه اقساط
    |--------------------------------------------------------------------------
    */

    const scheduleButton =
        document.getElementById(
            'show-schedule'
        );


    const scheduleCard =
        document.getElementById(
            'loan-schedule-card'
        );


    const scheduleBody =
        document.getElementById(
            'loan-schedule-body'
        );


    scheduleButton?.addEventListener(
        'click',
        () => {

            const schedule =
                JSON.parse(
                    scheduleButton.dataset.schedule ??
                    '[]'
                );


            if (!scheduleBody) {
                return;
            }


            scheduleBody.innerHTML =
                '';


            /*
            |--------------------------------------------------------------------------
            | جدول اقساط
            |--------------------------------------------------------------------------
            */

            schedule.forEach(
                item => {

                    scheduleBody.innerHTML += `

<tr>

<td class="text-center fw-bold">
    ${item.number}
    </td>

<td class="text-center">
    ${item.date}
</td>

<td class="text-center fw-bold">
    ${item.amount} ریال
</td>

</tr>

`;
                }
            );


            /*
            |--------------------------------------------------------------------------
            | تعداد اقساط
            |--------------------------------------------------------------------------
            */

            const totalCount =
                document.getElementById(
                    'schedule-total-count'
                );


            if (totalCount) {

                totalCount.textContent =
                    schedule.length;
            }


            /*
            |--------------------------------------------------------------------------
            | جمع مبلغ اقساط
            |--------------------------------------------------------------------------
            */

            let total = 0;


            schedule.forEach(
                item => {

                    total += Number(
                        String(
                            item.amount ?? '0'
                        )
                            .replace(/,/g, '')
                    );
                }
            );


            const totalAmount =
                document.getElementById(
                    'schedule-total-amount'
                );


            if (totalAmount) {

                totalAmount.textContent =
                    total.toLocaleString('en-US')
                    + ' ریال';
            }


            /*
            |--------------------------------------------------------------------------
            | نمایش کارت
            |--------------------------------------------------------------------------
            */

            scheduleCard?.classList.remove(
                'd-none'
            );


            scheduleCard?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    );


    /*
    |--------------------------------------------------------------------------
    | اجرای اولیه محاسبه
    |--------------------------------------------------------------------------
    */

    calculateLoan();

});
