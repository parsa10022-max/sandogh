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
