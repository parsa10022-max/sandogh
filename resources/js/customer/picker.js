/*
|--------------------------------------------------------------------------
| Customer Picker
|--------------------------------------------------------------------------
*/

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.customer-picker').forEach((picker) => {

        const codeInput  = picker.querySelector('.customer-code');
        const searchBtn  = picker.querySelector('.customer-search-btn');
        const hiddenInput = picker.querySelector('.customer-id');

        const resultBox  = picker.querySelector('.customer-result');
        const errorBox   = picker.querySelector('.customer-error');

        const nameBox    = picker.querySelector('.customer-name');
        const codeBox    = picker.querySelector('.customer-code-view');
        const mobileBox  = picker.querySelector('.customer-mobile');

        const searchUrl  = picker.dataset.searchUrl;

        let searchTimeout;

        function clearResult(clearHidden = true) {

            if (clearHidden) {
                hiddenInput.value = '';
            }

            resultBox.classList.add('d-none');
            errorBox.classList.add('d-none');
        }

        function showCustomer(customer) {

            hiddenInput.value = customer.id;

            nameBox.innerHTML = customer.name;
            codeBox.innerHTML = customer.code;
            mobileBox.innerHTML = customer.mobile ?? '';

            resultBox.classList.remove('d-none');
        }

        function searchCustomer() {

            const code = codeInput.value.trim();

            clearResult();

            if (code === '') {
                return;
            }

            fetch(`${searchUrl}?code=${encodeURIComponent(code)}`)
                .then(response => response.json())
                .then(data => {

                    if (!data.found) {

                        errorBox.innerHTML = data.message;
                        errorBox.classList.remove('d-none');

                        return;
                    }

                    showCustomer(data.customer);

                })
                .catch(() => {

                    errorBox.innerHTML = 'خطا در ارتباط با سرور';
                    errorBox.classList.remove('d-none');

                });
        }

        /*
        |--------------------------------------------------------------------------
        | حالت Edit
        |--------------------------------------------------------------------------
        | اگر کد مشتری نمایش داده شده ولی hidden خالی است
        | شناسه را دوباره از سرور دریافت کن.
        */

        if (codeInput.value.trim() !== '' && hiddenInput.value === '') {

            searchCustomer();

        } else if (hiddenInput.value !== '') {

            resultBox.classList.remove('d-none');

        }

        searchBtn.addEventListener('click', searchCustomer);

        codeInput.addEventListener('blur', function () {

            if (this.value.trim() !== '') {
                searchCustomer();
            }

        });

        codeInput.addEventListener('input', function () {

            clearTimeout(searchTimeout);

            hiddenInput.value = '';

            resultBox.classList.add('d-none');
            errorBox.classList.add('d-none');

            if (this.value.trim().length < 2) {
                return;
            }

            searchTimeout = setTimeout(() => {

                searchCustomer();

            }, 500);

        });

    });

});
