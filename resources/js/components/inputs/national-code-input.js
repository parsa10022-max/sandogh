import {
    toEnglishNumbers,
    onlyDigits,
    maxLength,
    trim
} from '../../utils/input-helper.js';


/**
 * --------------------------------------------------------------------------
 * National Code Input Component
 * --------------------------------------------------------------------------
 * Project : Sandogh
 * Laravel : 12
 * Bootstrap : 5
 * RTL
 * --------------------------------------------------------------------------
 */


document.addEventListener('DOMContentLoaded', () => {

    document
        .querySelectorAll('.national-code-input')
        .forEach(initNationalCodeInput);

});


/**
 * Initialize
 */
function initNationalCodeInput(input) {

    /*
     * ساختار جدید:
     *
     * .customer-form-field
     *      └── input
     *      ├── .national-code-feedback
     *      └── .status-icon i
     */

    const wrapper =
        input.closest('.customer-form-field')
        || input.closest('.mb-3')
        || input.closest('[class*="col-"]');


    if (!wrapper) {
        return;
    }


    const feedback =
        wrapper.querySelector(
            '.national-code-feedback'
        );


    const icon =
        wrapper.querySelector(
            '.status-icon i'
        );


    const live =
        input.dataset.live === 'true';


    /**
     * Keyboard
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
         * Ctrl / Cmd
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
     * مقدار اولیه
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
        maxLength(value, 10);


    return value;

}


/**
 * Clear
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
            'national-code-feedback';

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
     * خالی
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
                    'وارد کردن کد ملی الزامی است.';

            }


            if (icon) {

                icon.className =
                    'bi bi-x-circle-fill text-danger';

            }

        }

        return false;

    }


    /*
     * طول
     */
    if (value.length !== 10) {

        input.classList.add(
            'is-invalid'
        );


        if (feedback) {

            feedback.classList.add(
                'invalid-feedback'
            );

            feedback.textContent =
                'کد ملی باید دقیقاً 10 رقم باشد.';

        }


        if (icon) {

            icon.className =
                'bi bi-x-circle-fill text-danger';

        }


        return false;

    }


    /*
     * الگوریتم کد ملی
     */
    if (!isValidNationalCode(value)) {

        input.classList.add(
            'is-invalid'
        );


        if (feedback) {

            feedback.classList.add(
                'invalid-feedback'
            );

            feedback.textContent =
                'کد ملی معتبر نیست.';

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
            'کد ملی معتبر است.';

    }


    if (icon) {

        icon.className =
            'bi bi-check-circle-fill text-success';

    }


    return true;

}


/**
 * Official National Code Validation
 */
function isValidNationalCode(code) {

    if (!/^\d{10}$/.test(code)) {

        return false;

    }


    /*
     * تمام ارقام یکسان
     */
    if (/^(\d)\1{9}$/.test(code)) {

        return false;

    }


    let sum = 0;


    for (let i = 0; i < 9; i++) {

        sum +=
            parseInt(code[i], 10) *
            (10 - i);

    }


    const remainder =
        sum % 11;


    const checkDigit =
        parseInt(code[9], 10);


    if (remainder < 2) {

        return checkDigit === remainder;

    }


    return checkDigit ===
        (11 - remainder);

}
