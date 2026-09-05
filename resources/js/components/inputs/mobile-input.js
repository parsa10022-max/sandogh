import {
    toEnglishNumbers,
    onlyDigits,
    maxLength,
    trim
} from '../../utils/input-helper.js';


/**
 * --------------------------------------------------------------------------
 * Mobile Input Component
 * --------------------------------------------------------------------------
 * Project : Sandogh
 * Laravel : 12
 * Bootstrap : 5
 * RTL
 * --------------------------------------------------------------------------
 */


document.addEventListener('DOMContentLoaded', () => {

    document
        .querySelectorAll('.mobile-input')
        .forEach(initMobileInput);

});


/**
 * Initialize
 */
function initMobileInput(input) {

    /*
     * ساختار جدید:
     *
     * .customer-form-field
     *      └── input
     *      ├── .mobile-feedback
     *      └── .status-icon i
     *
     * ساختار قدیمی نیز به عنوان fallback پشتیبانی می‌شود.
     */

    const wrapper =
        input.closest('.customer-form-field')
        || input.closest('.mb-3')
        || input.closest('[class*="col-"]');

    if (!wrapper) {
        return;
    }


    const feedback =
        wrapper.querySelector('.mobile-feedback');


    const icon =
        wrapper.querySelector('.status-icon i');


    const live =
        input.dataset.live === 'true';


    /**
     * فقط اعداد
     */
    input.addEventListener('keydown', function (event) {

        const allow = [
            'Backspace',
            'Delete',
            'ArrowLeft',
            'ArrowRight',
            'ArrowUp',
            'ArrowDown',
            'Tab',
            'Home',
            'End'
        ];


        if (allow.includes(event.key)) {
            return;
        }


        /*
         * اجازه Ctrl + A / C / V / X
         */
        if (
            event.ctrlKey ||
            event.metaKey
        ) {
            return;
        }


        if (!/^\d$/.test(event.key)) {
            event.preventDefault();
        }

    });


    /**
     * Paste
     */
    input.addEventListener('paste', function () {

        setTimeout(() => {

            this.value =
                normalize(this.value);


            clear(
                this,
                feedback,
                icon
            );


            if (live) {

                validate(
                    this,
                    feedback,
                    icon
                );

            }

        }, 0);

    });


    /**
     * Input
     */
    input.addEventListener('input', function () {

        this.value =
            normalize(this.value);


        clear(
            this,
            feedback,
            icon
        );


        if (live) {

            validate(
                this,
                feedback,
                icon
            );

        }

    });


    /**
     * Blur
     */
    input.addEventListener('blur', function () {

        validate(
            this,
            feedback,
            icon
        );

    });


    /*
     * اگر مقدار اولیه وجود داشته باشد،
     * وضعیت آن را هنگام بارگذاری بررسی کن.
     */
    if (input.value !== '') {

        input.value =
            normalize(input.value);

    }

}


/**
 * Normalize
 */
function normalize(value) {

    value =
        trim(value);


    value =
        toEnglishNumbers(value);


    value =
        onlyDigits(value);


    value =
        maxLength(value, 11);


    /*
     * تبدیل 98xxxxxxxxxx
     * به 09xxxxxxxxx
     */
    if (value.startsWith('98')) {

        value =
            '0' + value.substring(2);

    }


    return value;

}


/**
 * Clear Validation
 */
function clear(
    input,
    feedback,
    icon
) {

    input.classList.remove(
        'is-valid',
        'is-invalid'
    );


    if (feedback) {

        feedback.className =
            'mobile-feedback';

        feedback.textContent =
            '';

    }


    if (icon) {

        icon.className =
            'bi';

    }

}


/**
 * Validate
 */
function validate(
    input,
    feedback,
    icon
) {

    clear(
        input,
        feedback,
        icon
    );


    const value =
        input.value;


    /*
     * اگر خالی است
     */
    if (value === '') {

        if (input.hasAttribute('required')) {

            input.classList.add(
                'is-invalid'
            );


            if (feedback) {

                feedback.classList.add(
                    'invalid-feedback'
                );

                feedback.textContent =
                    'وارد کردن شماره موبایل الزامی است.';

            }


            if (icon) {

                icon.className =
                    'bi bi-x-circle-fill text-danger';

            }

        }

        return false;

    }


    /*
     * شروع با 09
     */
    if (!value.startsWith('09')) {

        input.classList.add(
            'is-invalid'
        );


        if (feedback) {

            feedback.classList.add(
                'invalid-feedback'
            );

            feedback.textContent =
                'شماره موبایل باید با 09 شروع شود.';

        }


        if (icon) {

            icon.className =
                'bi bi-x-circle-fill text-danger';

        }


        return false;

    }


    /*
     * طول
     */
    if (value.length !== 11) {

        input.classList.add(
            'is-invalid'
        );


        if (feedback) {

            feedback.classList.add(
                'invalid-feedback'
            );

            feedback.textContent =
                'شماره موبایل باید 11 رقم باشد.';

        }


        if (icon) {

            icon.className =
                'bi bi-x-circle-fill text-danger';

        }


        return false;

    }


    /*
     * معتبر
     */
    input.classList.add(
        'is-valid'
    );


    if (feedback) {

        feedback.classList.add(
            'valid-feedback'
        );

        feedback.textContent =
            'شماره موبایل معتبر است.';

    }


    if (icon) {

        icon.className =
            'bi bi-check-circle-fill text-success';

    }


    return true;

}
