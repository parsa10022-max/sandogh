/**
 * --------------------------------------------------------------------------
 * Money Input Component
 * --------------------------------------------------------------------------
 * Version : 1.1.0
 * Author  : Project Sandogh
 * Laravel : 12
 * Bootstrap : 5
 * --------------------------------------------------------------------------
 */

class MoneyInput {

    constructor(element) {

        this.input = element;

        this.group = this.input.closest('.input-group');

        this.feedback = this.input
            .closest('.mb-3')
            ?.querySelector('.money-feedback');

        this.icon = this.group
            ?.querySelector('.status-icon i');

        this.live =
            this.input.dataset.live === 'true';

        this.required =
            this.input.hasAttribute('required');

        this.allowNegative =
            this.input.dataset.allowNegative === 'true';

        this.min =
            Number(this.input.dataset.min || 0);

        this.max =
            Number(this.input.dataset.max || 999999999999);

        this.init();
    }


    init() {

        this.formatInitialValue();

        this.bindEvents();
    }


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
     * Persian & Arabic Numbers
     */
    toEnglish(value) {

        const fa = '۰۱۲۳۴۵۶۷۸۹';
        const ar = '٠١٢٣٤٥٦٧٨٩';

        return value
            .replace(/[۰-۹]/g, d => fa.indexOf(d))
            .replace(/[٠-٩]/g, d => ar.indexOf(d));
    }


    /**
     * پاکسازی مقدار
     */
    clean(value) {

        value = this.toEnglish(
            value.toString()
        );

        value = value.replace(/,/g, '');

        const negative =
            value.trim().startsWith('-');

        if (value.includes('.')) {

            value = value.split('.')[0];

        }

        value = value.replace(/\D/g, '');

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
     * Thousands Separator
     */
    format(value) {

        value = this.clean(value);

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
            Number(numericValue).toLocaleString('en-US');

        return negative
            ? '-' + formatted
            : formatted;
    }


    /**
     * مقدار عددی
     */
    numericValue() {

        const value = this.rawValue();

        return value === ''
            ? 0
            : Number(value);
    }


    /**
     * Initial Value
     */
    formatInitialValue() {

        const value = this.input.value;

        if (!value) {

            return;

        }

        this.input.value =
            this.format(value);
    }


    /**
     * هنگام تایپ
     */
    handleInput() {

        const cursor =
            this.input.selectionStart;

        const oldLength =
            this.input.value.length;

        const value =
            this.clean(this.input.value);

        this.input.value =
            this.format(value);

        const newLength =
            this.input.value.length;

        const diff =
            newLength - oldLength;

        this.input.setSelectionRange(
            Math.max(0, cursor + diff),
            Math.max(0, cursor + diff)
        );

        if (this.live) {

            this.validate();

        }

        this.dispatch('money:input');
    }


    /**
     * خروج از فیلد
     */
    handleBlur() {

        this.validate();

        this.dispatch('money:blur');
    }


    /**
     * Paste
     */
    handlePaste(event) {

        event.preventDefault();

        const text = (
            event.clipboardData ||
            window.clipboardData
        ).getData('text');

        this.input.value =
            this.format(text);

        if (this.live) {

            this.validate();

        }

        this.dispatch('money:paste');
    }


    /**
     * فقط اعداد و علامت منفی
     */
    handleKeyPress(event) {

        const key = event.key;

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
     * Drag & Drop
     */
    handleDrop(event) {

        event.preventDefault();

        const text =
            event.dataTransfer.getData('text');

        this.input.value =
            this.format(text);

        this.validate();

        this.dispatch('money:drop');
    }


    /**
     * گرفتن مقدار خام
     */
    rawValue() {

        return this.clean(
            this.input.value
        );
    }


    /**
     * مقدار عددی
     */
    numericValue() {

        const value =
            this.rawValue();

        return value === ''
            ? 0
            : Number(value);
    }


    /**
     * مقدار فرمت شده
     */
    formattedValue() {

        return this.input.value;
    }


    /**
     * ثبت Event سفارشی
     */
    dispatch(name) {

        this.input.dispatchEvent(

            new CustomEvent(name, {

                bubbles: true,

                detail: {

                    value:
                        this.rawValue(),

                    formatted:
                        this.formattedValue(),

                    number:
                        this.numericValue()

                }

            })

        );
    }


    /**
     * اعتبارسنجی
     */
    validate() {

        const value =
            this.numericValue();

        let valid = true;

        let message = '';


        if (
            this.required &&
            value === 0
        ) {

            valid = false;

            message =
                'وارد کردن مبلغ الزامی است.';

        }

        else if (
            !this.allowNegative &&
            value < this.min
        ) {

            valid = false;

            message =
                `حداقل مبلغ ${this.min.toLocaleString('en-US')} است.`;

        }

        else if (
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
     * نمایش وضعیت اعتبارسنجی
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

            this.feedback.innerHTML = '';
        }


        if (this.icon) {

            this.icon.className = 'bi';
        }


        if (valid) {

            if (this.rawValue() !== '') {

                this.input.classList.add(
                    'is-valid'
                );

                if (this.icon) {

                    this.icon.classList.add(
                        'bi-check-circle-fill',
                        'text-success'
                    );
                }
            }

        } else {

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

                this.icon.classList.add(
                    'bi-x-circle-fill',
                    'text-danger'
                );
            }
        }
    }
}


/**
 * حذف کاما قبل از ارسال فرم
 */
document.addEventListener(
    'submit',
    function (event) {

        event.target
            .querySelectorAll('.money-input')
            .forEach(input => {

                input.value =
                    input.value.replace(/,/g, '');

            });

    },
    true
);


/**
 * راه‌اندازی خودکار
 */
document.addEventListener(
    'DOMContentLoaded',
    () => {

        document
            .querySelectorAll('.money-input')
            .forEach(input => {

                new MoneyInput(input);

            });

    }
);


export default MoneyInput;
