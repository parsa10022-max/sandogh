document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | نمایش نوع ضامن دوم
    |--------------------------------------------------------------------------
    */

    const guarantorTypeSelect = document.querySelector(
        '[name="guarantor2_type"]'
    );

    const customerBox = document.getElementById(
        'guarantor2-customer'
    );

    const borrowerBox = document.getElementById(
        'guarantor2-borrower'
    );

    const externalBox = document.getElementById(
        'guarantor2-external'
    );


    function updateGuarantorType() {

        const type = guarantorTypeSelect?.value;

        customerBox?.classList.add('d-none');
        borrowerBox?.classList.add('d-none');
        externalBox?.classList.add('d-none');

        if (type === 'customer') {
            customerBox?.classList.remove('d-none');
        }

        if (type === 'borrower') {
            borrowerBox?.classList.remove('d-none');
        }

        if (type === 'external') {
            externalBox?.classList.remove('d-none');
        }
    }


    if (guarantorTypeSelect) {

        guarantorTypeSelect.addEventListener(
            'change',
            updateGuarantorType
        );

        updateGuarantorType();
    }


    /*
    |--------------------------------------------------------------------------
    | نوع مدرک ضمانت
    |--------------------------------------------------------------------------
    */

    function setupGuaranteeType(
        selectName,
        fieldsId,
        numberId,
        accountId
    ) {

        const select = document.querySelector(
            `[name="${selectName}"]`
        );

        const fields = document.getElementById(
            fieldsId
        );

        const numberBox = document.getElementById(
            numberId
        );

        const accountBox = document.getElementById(
            accountId
        );


        if (!select) {
            return;
        }


        const amountInput = fields?.querySelector(
            '.money-input'
        );

        const numberInput = numberBox?.querySelector(
            'input'
        );

        const accountInput = accountBox?.querySelector(
            'input'
        );


        /*
        |--------------------------------------------------------------------------
        | عنوان سریال
        |--------------------------------------------------------------------------
        */

        function updateSerialLabel(labelText) {

            const label = numberBox?.querySelector(
                '.form-label'
            );

            if (!label) {
                return;
            }

            const textNodes = Array.from(
                label.childNodes
            ).filter(
                node =>
                    node.nodeType === Node.TEXT_NODE &&
                    node.textContent.trim() !== ''
            );

            if (textNodes.length) {

                textNodes[0].textContent =
                    `${labelText} `;

            }
        }


        /*
        |--------------------------------------------------------------------------
        | پاک کردن اطلاعات ضمانت
        |--------------------------------------------------------------------------
        */

        function clearGuaranteeFields() {

            if (amountInput) {
                amountInput.value = '';
            }

            if (numberInput) {
                numberInput.value = '';
            }

            if (accountInput) {
                accountInput.value = '';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | شماره حساب
        |--------------------------------------------------------------------------
        |
        | مهم:
        | هنگام اجرای اولیه نباید مقدار موجود در Input پاک شود.
        | فقط disabled/enabled و نمایش/عدم نمایش کنترل می‌شود.
        |
        */

        function showAccount() {

            accountBox?.classList.remove(
                'd-none'
            );

            if (accountInput) {
                accountInput.disabled = false;
            }
        }


        function hideAccount(clearValue = false) {

            accountBox?.classList.add(
                'd-none'
            );

            if (accountInput) {

                if (clearValue) {
                    accountInput.value = '';
                }

                accountInput.disabled = true;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | مبلغ + سریال
        |--------------------------------------------------------------------------
        */

        function showGuaranteeFields() {

            fields?.classList.remove(
                'd-none'
            );

            numberBox?.classList.remove(
                'd-none'
            );
        }


        function hideGuaranteeFields() {

            fields?.classList.add(
                'd-none'
            );

            numberBox?.classList.add(
                'd-none'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | بروزرسانی بر اساس نوع ضمانت
        |--------------------------------------------------------------------------
        */

        function updateGuaranteeFields() {

            const type = select.value;


            /*
            |--------------------------------------------------------------------------
            | وضعیت پایه
            |--------------------------------------------------------------------------
            |
            | اینجا فقط وضعیت نمایش و disabled را تنظیم می‌کنیم.
            | مقدار شماره حساب را پاک نمی‌کنیم.
            |
            */

            hideGuaranteeFields();
            hideAccount(false);


            /*
            |--------------------------------------------------------------------------
            | چک
            |--------------------------------------------------------------------------
            */

            if (type === 'check') {

                showGuaranteeFields();

                showAccount();

                updateSerialLabel(
                    'سریال چک'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | سفته
            |--------------------------------------------------------------------------
            */

            if (type === 'promissory_note') {

                showGuaranteeFields();

                hideAccount(true);

                updateSerialLabel(
                    'سریال سفته'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | سایر
            |--------------------------------------------------------------------------
            */

            clearGuaranteeFields();

            hideGuaranteeFields();

            hideAccount(true);
        }


        /*
        |--------------------------------------------------------------------------
        | تغییر نوع ضمانت
        |--------------------------------------------------------------------------
        */

        select.addEventListener(
            'change',
            updateGuaranteeFields
        );


        /*
        |--------------------------------------------------------------------------
        | اجرای اولیه
        |--------------------------------------------------------------------------
        */

        updateGuaranteeFields();
    }


    /*
    |--------------------------------------------------------------------------
    | ضامن اول
    |--------------------------------------------------------------------------
    */

    setupGuaranteeType(
        'guarantor1_guarantee_type',
        'guarantor1-guarantee-fields',
        'guarantor1-guarantee-number',
        'guarantor1-guarantee-account'
    );


    /*
    |--------------------------------------------------------------------------
    | ضامن دوم
    |--------------------------------------------------------------------------
    */

    setupGuaranteeType(
        'guarantor2_guarantee_type',
        'guarantor2-guarantee-fields',
        'guarantor2-guarantee-number',
        'guarantor2-guarantee-account'
    );

});
