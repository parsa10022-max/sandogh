/**
 * --------------------------------------------------------------------------
 * Money Input Component
 * --------------------------------------------------------------------------
 * Version : 1.2.0
 * Project : Sandogh
 * Laravel : 12
 * Bootstrap : 5
 * RTL
 * --------------------------------------------------------------------------
 */


class MoneyInput {

    constructor(element) {

        this.input =
            element;


        /*
         * ساختار جدید
         */
        this.field =
            this.input.closest(
                '.customer-form-field'
            )
            || this.input.closest(
                '.mb-3'
            )
            || this.input.closest(
                '.input-group'
            );


        /*
         * ساختار قدیمی input-group
         */
        this.group =
            this.input.closest(
                '.input-group'
            );


        /*
         * Feedback
         */
        this.feedback =
            this.field?.querySelector(
                '.money-feedback'
            )
            || this.group?.querySelector(
                '.money-feedback'
            );


        /*
         * Status Icon
         */
        this.icon =
            this.field?.querySelector(
                '.status-icon i'
            )
            || this.group?.querySelector(
                '.status-icon i'
            );


        this.live =
            this.input.dataset.live === 'true';


        this.required =
            this.input.hasAttribute(
                'required'
            );


        this.allowNegative =
            this.input.dataset.allowNegative === 'true';


        this.min =
            Number(
                this.input.dataset.min || 0
            );


        this.max =
            Number(
                this.input.dataset.max ||
                999999999999
            );


        this.init();

    }


    /**
     * Initialize
     */
    init() {

        this.formatInitialValue();

        this.bindEvents();

    }


    /**
     * Events
     */
    bindEvents() {

        this.input.addEventListener(
            'input',
            this.handleInput.bind(this)
        );


        this.input.addEventListener(
            'blur',
            this.handleBlur.bind(this)
        );


        this.input.addEventListener(
            'paste',
            this.handlePaste.bind(this)
        );


        this.input.addEventListener(
            'keypress',
            this.handleKeyPress.bind(this)
        );


        this.input.addEventListener(
            'drop',
            this.handleDrop.bind(this)
        );

    }


    /**
     * Persian / Arabic → English
     */
    toEnglish(value) {

        const fa =
            '۰۱۲۳۴۵۶۷۸۹';


        const ar =
            '٠١٢٣٤٥٦٧٨٩';


        return value
            .replace(
                /[۰-۹]/g,
                digit => fa.indexOf(digit)
            )
            .replace(
                /[٠-٩]/g,
                digit => ar.indexOf(digit)
            );

    }


    /**
     * Clean
     */
    clean(value) {

        value =
            this.toEnglish(
                value.toString()
            );


        value =
            value.replace(
                /,/g,
                ''
            );


        const negative =
            value
                .trim()
                .startsWith('-');


        /*
         * اعشار حذف شود
         */
        if (value.includes('.')) {

            value =
                value.split('.')[0];

        }


        /*
         * فقط اعداد
         */
        value =
            value.replace(
                /\D/g,
                ''
            );


        if (value === '') {

            return '';

        }


        if (
            negative &&
            this.allowNegative
        ) {

            return '-' + value;

        }


        return value;

    }


    /**
     * Format
     */
    format(value) {

        value =
            this.clean(value);


        if (value === '') {

            return '';

        }


        const negative =
            value.startsWith('-');


        const numericValue =
            negative
                ? value.substring(1)
                : value;


        const formatted =
            Number(
                numericValue
            ).toLocaleString(
                'en-US'
            );


        return negative
            ? '-' + formatted
            : formatted;

    }


    /**
     * Raw Value
     */
    rawValue() {

        return this.clean(
            this.input.value
        );

    }


    /**
     * Numeric Value
     */
    numericValue() {

        const value =
            this.rawValue();


        return value === ''
            ? 0
            : Number(value);

    }


    /**
     * Formatted Value
     */
    formattedValue() {

        return this.input.value;

    }


    /**
     * Initial
     */
    formatInitialValue() {

        const value =
            this.input.value;


        if (!value) {

            return;

        }


        this.input.value =
            this.format(value);

    }


    /**
     * Input
     */
    handleInput() {

        const cursor =
            this.input.selectionStart;


        const oldLength =
            this.input.value.length;


        const value =
            this.clean(
                this.input.value
            );


        this.input.value =
            this.format(value);


        const newLength =
            this.input.value.length;


        const diff =
            newLength - oldLength;


        const position =
            Math.max(
                0,
                cursor + diff
            );


        try {

            this.input.setSelectionRange(
                position,
                position
            );

        } catch (error) {

            /*
             * بعض inputها ممکن است
             * selection پشتیبانی نکنند.
             */

        }


        if (this.live) {

            this.validate();

        }


        this.dispatch(
            'money:input'
        );

    }


    /**
     * Blur
     */
    handleBlur() {

        this.validate();


        this.dispatch(
            'money:blur'
        );

    }


    /**
     * Paste
     */
    handlePaste(event) {

        event.preventDefault();


        const text =
            (
                event.clipboardData ||
                window.clipboardData
            )
                .getData('text');


        this.input.value =
            this.format(text);


        if (this.live) {

            this.validate();

        }


        this.dispatch(
            'money:paste'
        );

    }


    /**
     * Keypress
     */
    handleKeyPress(event) {

        const key =
            event.key;


        const allowed =
            /^[0-9۰-۹٠-٩]$/;


        if (allowed.test(key)) {

            return;

        }


        if (
            key === '-' &&
            this.allowNegative &&
            this.input.selectionStart === 0 &&
            !this.input.value.includes('-')
        ) {

            return;

        }


        event.preventDefault();

    }


    /**
     * Drop
     */
    handleDrop(event) {

        event.preventDefault();


        const text =
            event.dataTransfer.getData(
                'text'
            );


        this.input.value =
            this.format(text);


        this.validate();


        this.dispatch(
            'money:drop'
        );

    }


    /**
     * Validation
     */
    validate() {

        const raw =
            this.rawValue();


        const value =
            raw === ''
                ? 0
                : Number(raw);


        let valid = true;

        let message = '';


        /*
         * Required
         */
        if (
            this.required &&
            raw === ''
        ) {

            valid = false;

            message =
                'وارد کردن مبلغ الزامی است.';

        }


        /*
         * Minimum
         */
        else if (
            raw !== '' &&
            value < this.min
        ) {

            valid = false;

            message =
                `حداقل مبلغ ${this.min.toLocaleString('en-US')} است.`;

        }


        /*
         * Maximum
         */
        else if (
            raw !== '' &&
            value > this.max
        ) {

            valid = false;

            message =
                `حداکثر مبلغ ${this.max.toLocaleString('en-US')} است.`;

        }


        this.showValidation(
            valid,
            message
        );


        return valid;

    }


    /**
     * Show Validation
     */
    showValidation(
        valid,
        message = ''
    ) {

        this.input.classList.remove(
            'is-valid',
            'is-invalid'
        );


        if (this.feedback) {

            this.feedback.classList.remove(
                'valid-feedback',
                'invalid-feedback',
                'd-block'
            );


            this.feedback.innerHTML =
                '';

        }


        if (this.icon) {

            this.icon.className =
                'bi';

        }


        /*
         * معتبر
         */
        if (valid) {

            if (this.rawValue() !== '') {

                this.input.classList.add(
                    'is-valid'
                );


                if (this.feedback) {

                    this.feedback.classList.add(
                        'valid-feedback'
                    );


                    this.feedback.textContent =
                        'مبلغ معتبر است.';

                }


                if (this.icon) {

                    this.icon.className =
                        'bi bi-check-circle-fill text-success';

                }

            }


            return;

        }


        /*
         * نامعتبر
         */
        this.input.classList.add(
            'is-invalid'
        );


        if (this.feedback) {

            this.feedback.innerHTML =
                message;


            this.feedback.classList.add(
                'invalid-feedback',
                'd-block'
            );

        }


        if (this.icon) {

            this.icon.className =
                'bi bi-x-circle-fill text-danger';

        }

    }


    /**
     * Custom Event
     */
    dispatch(name) {

        this.input.dispatchEvent(

            new CustomEvent(
                name,
                {
                    bubbles: true,

                    detail: {

                        value:
                            this.rawValue(),

                        formatted:
                            this.formattedValue(),

                        number:
                            this.numericValue()

                    }
                }
            )

        );

    }

}


/**
 * --------------------------------------------------------------------------
 * حذف کاما قبل از ارسال فرم
 * --------------------------------------------------------------------------
 */

document.addEventListener(
    'submit',
    function (event) {

        event.target
            .querySelectorAll(
                '.money-input'
            )
            .forEach(input => {

                input.value =
                    input.value
                        .replace(
                            /,/g,
                            ''
                        );

            });

    },
    true
);


/**
 * --------------------------------------------------------------------------
 * Auto Initialize
 * --------------------------------------------------------------------------
 */

document.addEventListener(
    'DOMContentLoaded',
    () => {

        document
            .querySelectorAll(
                '.money-input'
            )
            .forEach(input => {

                /*
                 * جلوگیری از initialize دوباره
                 */
                if (
                    input.dataset.moneyInitialized === 'true'
                ) {

                    return;

                }


                input.dataset.moneyInitialized =
                    'true';


                new MoneyInput(input);

            });

    }
);


export default MoneyInput;
