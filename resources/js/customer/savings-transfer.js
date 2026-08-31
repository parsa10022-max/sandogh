/* =========================================================
   CUSTOMER — SAVINGS TRANSFER
   شماره حساب + جستجو + مبلغ
   ========================================================= */

document.addEventListener('DOMContentLoaded', function () {

    console.log('Savings Transfer JS Loaded');


    /* =========================================================
       ELEMENTS
       ========================================================= */

    const searchButton =
        document.getElementById('search_customer');

    const customerKeyword =
        document.getElementById('customer_keyword');

    const resultBox =
        document.querySelector(
            '.customer-savings-transfer-result'
        );

    const receiverCustomerId =
        document.getElementById(
            'receiver_customer_id'
        );

    const paymentButton =
        document.getElementById(
            'payment_button'
        );

    const amountDisplay =
        document.getElementById(
            'amount_display'
        );

    const amountInput =
        document.getElementById(
            'amount'
        );


    if (!searchButton || !customerKeyword) {
        return;
    }


    console.log('Search button found');


    /* =========================================================
       HELPERS
       ========================================================= */

    function toEnglishDigits(value) {

        return value
            .replace(/[۰-۹]/g, function (digit) {
                return '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit);
            })
            .replace(/[٠-٩]/g, function (digit) {
                return '٠١٢٣٤٥٦٧٨٩'.indexOf(digit);
            });

    }


    function onlyDigits(value) {

        return toEnglishDigits(value)
            .replace(/\D/g, '');

    }


    /* =========================================================
       ACCOUNT NUMBER NORMALIZE

       ورودی‌های مجاز:

       6111-000011
       6111000011
       000011

       خروجی:

       6111000011
       ========================================================= */

    function normalizeAccountNumber(value) {

        value = onlyDigits(value);


        /*
         * اگر کاربر شماره کامل حساب را وارد کرده
         * همان شماره استفاده می‌شود.
         */
        if (value.length === 10) {

            return value;

        }


        /*
         * اگر فقط 6 رقم انتهایی وارد شده باشد
         * پیشوند 6111 اضافه می‌شود.
         */
        if (value.length === 6) {

            return '6111' + value;

        }


        /*
         * سایر حالت‌ها نامعتبر هستند.
         */
        return value;

    }


    /* =========================================================
       ACCOUNT INPUT

       فقط نمایش را مرتب می‌کنیم.

       6111000011
       ↓
       6111-000011

       اما مقدار واقعی هنگام جستجو:
       6111000011
       ========================================================= */

    customerKeyword.addEventListener(
        'input',
        function () {

            let value =
                onlyDigits(this.value);


            /*
             * حداکثر 10 رقم
             */
            value =
                value.substring(0, 10);


            /*
             * اگر 10 رقم باشد:
             *
             * 6111000011
             *
             * نمایش:
             *
             * 6111-000011
             */
            if (value.length > 4) {

                value =
                    value.substring(0, 4)
                    + '-'
                    + value.substring(4);

            }


            this.value = value;


            /*
             * با تغییر شماره حساب،
             * نتیجه قبلی و شناسه مقصد پاک شود.
             */

            if (receiverCustomerId) {

                receiverCustomerId.value = '';

            }


            if (paymentButton) {

                paymentButton.disabled = true;

            }

            if (resultBox) {

                resultBox.className =
                    'customer-savings-transfer-result d-none';

                resultBox.innerHTML = '';

            }

        }
    );


    /* =========================================================
       SEARCH
       ========================================================= */

    searchButton.addEventListener(
        'click',
        async function () {

            console.log('Search button CLICKED');


            /*
             * جلوگیری از چند کلیک همزمان
             */
            if (searchButton.disabled) {

                return;

            }


            const rawKeyword =
                customerKeyword.value;


            const keyword =
                normalizeAccountNumber(
                    rawKeyword
                );


            console.log(
                'Raw Keyword:',
                rawKeyword
            );

            console.log(
                'Normalized Keyword:',
                keyword
            );


            /*
             * فقط شماره حساب 6 یا 10 رقمی
             */
            const digits =
                onlyDigits(rawKeyword);


            if (
                digits.length !== 6 &&
                digits.length !== 10
            ) {

                showError(
                    'شماره حساب پس‌انداز را به صورت صحیح وارد کنید.'
                );

                return;

            }


            /*
             * پاک کردن نتیجه قبلی
             */

            if (resultBox) {

                resultBox.className =
                    'customer-savings-transfer-result d-none';

                resultBox.innerHTML = '';

            }


            if (receiverCustomerId) {

                receiverCustomerId.value = '';

            }


            if (paymentButton) {

                paymentButton.disabled = true;

            }


            /*
             * Loading
             */

            searchButton.disabled = true;

            const originalButtonHTML =
                searchButton.innerHTML;

            searchButton.innerHTML = `
                <span class="spinner-border spinner-border-sm"
                      role="status"
                      aria-hidden="true">
                </span>

                <span>
                    جستجو...
                </span>
            `;


            try {

                const response =
                    await fetch(
                        searchUrl,
                        {

                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                csrfToken

                            },

                            body:
                                JSON.stringify({

                                    keyword: keyword

                                })

                        }
                    );


                /*
                 * بررسی HTTP
                 */

                if (!response.ok) {

                    throw new Error(
                        'خطا در ارتباط با سرور'
                    );

                }


                const data =
                    await response.json();


                console.log(
                    'Search Response:',
                    data
                );


                /* =================================================
                   FOUND
                   ================================================= */

                if (data.found) {

                    if (receiverCustomerId) {

                        receiverCustomerId.value =
                            data.customer.id;

                    }


                    showSuccess(
                        data.customer
                    );


                    if (paymentButton) {

                        paymentButton.disabled = false;

                    }

                }


                /* =================================================
                   NOT FOUND
                   ================================================= */

                else {

                    showError(
                        data.message ||
                        'حساب پس‌انداز مورد نظر پیدا نشد.'
                    );

                }


            } catch (error) {

                console.error(
                    'Savings Transfer Search Error:',
                    error
                );


                showError(
                    'در جستجوی حساب خطایی رخ داد. دوباره تلاش کنید.'
                );


            } finally {

                /*
                 * خیلی مهم:
                 *
                 * دکمه همیشه به حالت عادی برمی‌گردد.
                 */

                searchButton.disabled = false;

                searchButton.innerHTML =
                    originalButtonHTML;

            }

        }
    );


    /* =========================================================
       SUCCESS RESULT
       ========================================================= */

    function showSuccess(customer) {

        if (!resultBox) {

            return;

        }


        resultBox.className =
            'customer-savings-transfer-result ' +
            'customer-savings-transfer-result-success';


        resultBox.innerHTML = `

            <div class="customer-savings-transfer-result-icon">

                <i class="bi bi-check-circle-fill"></i>

            </div>


            <div class="customer-savings-transfer-result-content">

                <span>
                    حساب مقصد پیدا شد
                </span>

                <strong>
                    ${escapeHtml(customer.name)}
                </strong>

                <small>
                    شماره حساب:
                    <b dir="ltr">
                        ${formatAccountNumber(
            customer.account_number ||
            customer.account ||
            ''
        )}
                    </b>
                </small>

            </div>

        `;

    }


    /* =========================================================
       ERROR RESULT
       ========================================================= */

    function showError(message) {

        if (!resultBox) {

            return;

        }


        resultBox.className =
            'customer-savings-transfer-result ' +
            'customer-savings-transfer-result-error';


        resultBox.innerHTML = `

            <div class="customer-savings-transfer-result-icon">

                <i class="bi bi-exclamation-circle-fill"></i>

            </div>


            <div class="customer-savings-transfer-result-content">

                <span>
                    نتیجه جستجو
                </span>

                <strong>
                    ${escapeHtml(message)}
                </strong>

            </div>

        `;

    }


    /* =========================================================
       FORMAT ACCOUNT NUMBER

       6111000011
       ↓
       6111-000011
       ========================================================= */

    function formatAccountNumber(value) {

        value =
            onlyDigits(String(value));


        if (value.length === 10) {

            return (
                value.substring(0, 4)
                + '-'
                + value.substring(4)
            );

        }


        return value;

    }


    /* =========================================================
       AMOUNT

       نمایش:

       50000
       ↓
       50,000

       مقدار hidden:

       50000
       ========================================================= */

    if (amountDisplay && amountInput) {

        amountDisplay.addEventListener(
            'input',
            function () {

                let value =
                    onlyDigits(this.value);


                /*
                 * حذف صفرهای اضافی ابتدای عدد
                 *
                 * 00050000
                 * ↓
                 * 50000
                 */

                value =
                    value.replace(/^0+(?=\d)/, '');


                /*
                 * مقدار واقعی برای Laravel
                 */

                amountInput.value =
                    value;


                /*
                 * مقدار نمایشی سه رقم سه رقم
                 */

                if (value) {

                    this.value =
                        Number(value)
                            .toLocaleString('en-US');

                } else {

                    this.value = '';

                }

            }
        );


        /*
         * هنگام ورود مجدد صفحه
         */

        amountDisplay.addEventListener(
            'focus',
            function () {

                const value =
                    onlyDigits(this.value);


                if (value) {

                    this.value =
                        Number(value)
                            .toLocaleString('en-US');

                }

            }
        );

    }


    /* =========================================================
       FORM SUBMIT

       اطمینان از اینکه amount بدون کاما ارسال شود.
       ========================================================= */

    const form =
        document.getElementById(
            'savings-transfer-form'
        );


    if (form) {

        form.addEventListener(
            'submit',
            function () {

                if (amountDisplay && amountInput) {

                    amountInput.value =
                        onlyDigits(
                            amountDisplay.value
                        );

                }

            }
        );

    }


    /* =========================================================
       HTML ESCAPE
       ========================================================= */

    function escapeHtml(value) {

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }

});
