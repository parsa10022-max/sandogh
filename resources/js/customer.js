document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.js-money-input').forEach(function (input) {

        input.addEventListener('input', function () {

            let value = this.value.replace(/\D/g, '');

            if (!value) {
                this.value = '';
                return;
            }

            this.value = Number(value).toLocaleString('en-US');

        });

    });


    document.querySelectorAll('form').forEach(function (form) {

        form.addEventListener('submit', function () {

            form.querySelectorAll('.js-money-input').forEach(function (input) {

                input.value = input.value.replace(/,/g, '');

            });

        });

    });

});
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.customer-settings-password-toggle')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const wrapper = button.closest(
                    '.customer-settings-password-wrapper'
                );

                const input = wrapper.querySelector(
                    '.customer-settings-password-input'
                );

                const icon = button.querySelector('i');

                if (input.type === 'password') {

                    input.type = 'text';

                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');

                    button.setAttribute(
                        'aria-label',
                        'مخفی کردن رمز عبور'
                    );

                    button.setAttribute(
                        'title',
                        'مخفی کردن رمز عبور'
                    );

                } else {

                    input.type = 'password';

                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');

                    button.setAttribute(
                        'aria-label',
                        'نمایش رمز عبور'
                    );

                    button.setAttribute(
                        'title',
                        'نمایش رمز عبور'
                    );
                }
            });
        });

});
