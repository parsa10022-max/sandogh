/*
|--------------------------------------------------------------------------
| Customer Picker
|--------------------------------------------------------------------------
*/

console.log('CUSTOMER PICKER LOADED');

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.customer-picker').forEach((picker) => {

        const codeInput =
            picker.querySelector('.customer-code');

        const searchBtn =
            picker.querySelector('.customer-search-btn');

        const hiddenInput =
            picker.querySelector('.customer-id');

        const resultBox =
            picker.querySelector('.customer-result');

        const errorBox =
            picker.querySelector('.customer-error');

        const nameBox =
            picker.querySelector('.customer-name');

        const codeBox =
            picker.querySelector('.customer-code-view');

        const mobileBox =
            picker.querySelector('.customer-mobile');

        const searchUrl =
            picker.dataset.searchUrl;

        let searchTimeout;


        /*
        |--------------------------------------------------------------------------
        | پاک کردن نتیجه
        |--------------------------------------------------------------------------
        */

        function clearResult(clearHidden = true) {

            if (clearHidden) {
                hiddenInput.value = '';
            }

            resultBox?.classList.add('d-none');

            errorBox?.classList.add('d-none');

            if (errorBox) {
                errorBox.textContent = '';
            }
        }


        /*
        |--------------------------------------------------------------------------
        | نمایش مشتری
        |--------------------------------------------------------------------------
        */

        function showCustomer(customer) {

            if (!customer?.id) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ذخیره شناسه مشتری
            |--------------------------------------------------------------------------
            */

            hiddenInput.value =
                customer.id;


            /*
            |--------------------------------------------------------------------------
            | نمایش اطلاعات
            |--------------------------------------------------------------------------
            */

            if (nameBox) {

                nameBox.textContent =
                    customer.name ?? '';

            }

            if (codeBox) {

                codeBox.textContent =
                    customer.code ?? '';

            }

            if (mobileBox) {

                mobileBox.textContent =
                    customer.mobile ?? '';

            }


            /*
            |--------------------------------------------------------------------------
            | نمایش نتیجه
            |--------------------------------------------------------------------------
            */

            resultBox?.classList.remove('d-none');

            errorBox?.classList.add('d-none');


            /*
            |--------------------------------------------------------------------------
            | اطلاع به سایر کامپوننت‌ها
            |--------------------------------------------------------------------------
            |
            | مثال:
            |
            | customer_id
            | guarantor1_customer_id
            | guarantor2_customer_id
            |
            |--------------------------------------------------------------------------
            */

            picker.dispatchEvent(
                new CustomEvent('customer:selected', {
                    bubbles: true,

                    detail: {

                        customer: customer,

                        name: hiddenInput.name,

                    },

                })
            );

        }


        /*
        |--------------------------------------------------------------------------
        | جستجوی مشتری
        |--------------------------------------------------------------------------
        */

        function searchCustomer() {

            const code =
                codeInput.value.trim();

            clearResult();


            if (code === '') {
                return;
            }


            console.log(
                'SEARCH URL:',
                searchUrl
            );

            console.log(
                'CUSTOMER CODE:',
                code
            );


            fetch(
                `${searchUrl}?code=${encodeURIComponent(code)}`,
                {
                    headers: {

                        'Accept':
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',

                    },
                }
            )
                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            `HTTP ${response.status}`
                        );

                    }

                    return response.json();

                })
                .then(data => {

                    console.log(
                        'CUSTOMER RESPONSE:',
                        data
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | مشتری پیدا نشد
                    |--------------------------------------------------------------------------
                    */

                    if (!data.found) {

                        errorBox.textContent =
                            data.message ??
                            'مشتری یافت نشد.';

                        errorBox.classList.remove(
                            'd-none'
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | مشتری پیدا شد
                    |--------------------------------------------------------------------------
                    */

                    showCustomer(
                        data.customer
                    );

                })
                .catch(error => {

                    console.error(
                        'CUSTOMER SEARCH ERROR:',
                        error
                    );


                    errorBox.textContent =
                        'خطا در ارتباط با سرور.';

                    errorBox.classList.remove(
                        'd-none'
                    );

                });

        }


        /*
        |--------------------------------------------------------------------------
        | Edit mode
        |--------------------------------------------------------------------------
        */

        if (
            codeInput.value.trim() !== '' &&
            hiddenInput.value === ''
        ) {

            searchCustomer();

        } else if (
            hiddenInput.value !== ''
        ) {

            resultBox?.classList.remove(
                'd-none'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Search button
        |--------------------------------------------------------------------------
        */

        searchBtn?.addEventListener(
            'click',
            searchCustomer
        );


        /*
        |--------------------------------------------------------------------------
        | Blur
        |--------------------------------------------------------------------------
        */

        codeInput?.addEventListener(
            'blur',
            function () {

                if (
                    this.value.trim() !== ''
                ) {

                    searchCustomer();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Input
        |--------------------------------------------------------------------------
        */

        codeInput?.addEventListener(
            'input',
            function () {

                clearTimeout(
                    searchTimeout
                );


                /*
                |--------------------------------------------------------------------------
                | با تغییر کد، مشتری قبلی دیگر معتبر نیست
                |--------------------------------------------------------------------------
                */

                hiddenInput.value = '';


                resultBox?.classList.add(
                    'd-none'
                );

                errorBox?.classList.add(
                    'd-none'
                );


                if (
                    this.value.trim().length < 2
                ) {

                    return;

                }


                searchTimeout =
                    setTimeout(
                        searchCustomer,
                        500
                    );

            }
        );

    });

});
