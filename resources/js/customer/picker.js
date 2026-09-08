
/*
|--------------------------------------------------------------------------
| Customer Picker
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.customer-picker').forEach((picker) => {

        const codeInput   = picker.querySelector('.customer-code');
        const searchBtn   = picker.querySelector('.customer-search-btn');
        const hiddenInput = picker.querySelector('.customer-id');

        const resultBox   = picker.querySelector('.customer-result');
        const errorBox    = picker.querySelector('.customer-error');

        const nameBox     = picker.querySelector('.customer-name');
        const codeBox     = picker.querySelector('.customer-code-view');
        const mobileBox   = picker.querySelector('.customer-mobile');

        const searchUrl   = picker.dataset.searchUrl;

        let searchTimeout = null;


        /*
        |--------------------------------------------------------------------------
        | پاک کردن نتیجه
        |--------------------------------------------------------------------------
        */

        function clearResult(clearHidden = true) {

            if (clearHidden && hiddenInput) {
                hiddenInput.value = '';
            }

            resultBox?.classList.add('d-none');
            errorBox?.classList.add('d-none');

        }


        /*
        |--------------------------------------------------------------------------
        | نمایش خطا
        |--------------------------------------------------------------------------
        */

        function showError(message) {

            if (!errorBox) {
                return;
            }

            errorBox.textContent =
                message || 'مشتری مورد نظر پیدا نشد.';

            errorBox.classList.remove('d-none');

        }


        /*
        |--------------------------------------------------------------------------
        | نمایش اطلاعات مشتری
        |--------------------------------------------------------------------------
        */

        function showCustomer(customer) {

            if (!customer) {
                showError('اطلاعات مشتری دریافت نشد.');
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | ID
            |--------------------------------------------------------------------------
            */

            hiddenInput.value =
                customer.id ?? '';


            /*
            |--------------------------------------------------------------------------
            | نام و نام خانوادگی
            |--------------------------------------------------------------------------
            */

            let name =
                customer.full_name
                ?? customer.name
                ?? '';


            /*
            | اگر full_name / name وجود نداشت،
            | از first_name و last_name بساز
            */

            if (!name) {

                name = [
                    customer.first_name ?? '',
                    customer.last_name ?? ''
                ]
                    .join(' ')
                    .trim();

            }


            /*
            |--------------------------------------------------------------------------
            | کد مشتری
            |--------------------------------------------------------------------------
            */

            const customerCode =
                customer.customer_code
                ?? customer.code
                ?? '';


            /*
            |--------------------------------------------------------------------------
            | موبایل
            |--------------------------------------------------------------------------
            */

            const mobile =
                customer.mobile
                ?? '';


            /*
            |--------------------------------------------------------------------------
            | نمایش
            |--------------------------------------------------------------------------
            */

            nameBox.textContent =
                name || '---';

            codeBox.textContent =
                customerCode || '---';

            mobileBox.textContent =
                mobile || '---';


            /*
            |--------------------------------------------------------------------------
            | نمایش کارت
            |--------------------------------------------------------------------------
            */

            resultBox.classList.remove('d-none');

            errorBox.classList.add('d-none');

        }


        /*
        |--------------------------------------------------------------------------
        | جستجوی مشتری
        |--------------------------------------------------------------------------
        */

        function searchCustomer() {

            const code =
                codeInput?.value.trim() ?? '';


            clearResult();


            if (code === '') {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | وضعیت Loading
            |--------------------------------------------------------------------------
            */

            if (searchBtn) {

                searchBtn.disabled = true;

                searchBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm" role="status"></span>';

            }


            fetch(
                `${searchUrl}?code=${encodeURIComponent(code)}`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
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

                    /*
                    |--------------------------------------------------------------------------
                    | مشتری پیدا نشده
                    |--------------------------------------------------------------------------
                    */

                    if (!data.found) {

                        showError(
                            data.message
                            ?? 'مشتری با این کد پیدا نشد.'
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | نمایش مشتری
                    |--------------------------------------------------------------------------
                    */

                    showCustomer(
                        data.customer
                    );

                })

                .catch(error => {

                    console.error(
                        'Customer Picker Error:',
                        error
                    );

                    showError(
                        'خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.'
                    );

                })

                .finally(() => {

                    if (searchBtn) {

                        searchBtn.disabled = false;

                        searchBtn.innerHTML =
                            '<i class="bi bi-search"></i>';

                    }

                });

        }


        /*
        |--------------------------------------------------------------------------
        | حالت Edit
        |--------------------------------------------------------------------------
        */

        if (
            codeInput?.value.trim() !== ''
            &&
            hiddenInput?.value === ''
        ) {

            searchCustomer();

        }


        /*
        |--------------------------------------------------------------------------
        | دکمه جستجو
        |--------------------------------------------------------------------------
        */

        searchBtn?.addEventListener(
            'click',
            searchCustomer
        );


        /*
        |--------------------------------------------------------------------------
        | خروج از فیلد
        |--------------------------------------------------------------------------
        */

        codeInput?.addEventListener(
            'blur',
            function () {

                if (this.value.trim() !== '') {
                    searchCustomer();
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | تایپ کد مشتری
        |--------------------------------------------------------------------------
        */

        codeInput?.addEventListener(
            'input',
            function () {

                clearTimeout(
                    searchTimeout
                );


                /*
                | با تغییر کد، مشتری قبلی دیگر معتبر نیست
                */

                hiddenInput.value = '';

                resultBox.classList.add('d-none');
                errorBox.classList.add('d-none');


                const code =
                    this.value.trim();


                /*
                | حداقل ۲ رقم
                */

                if (code.length < 2) {
                    return;
                }


                /*
                | جستجوی خودکار بعد از ۵۰۰ میلی‌ثانیه
                */

                searchTimeout =
                    setTimeout(() => {

                        searchCustomer();

                    }, 500);

            }
        );

    });

});

