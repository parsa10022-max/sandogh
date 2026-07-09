/**
 * --------------------------------------------------------------------------
 * Sheba Input Component
 * --------------------------------------------------------------------------
 * Version : 1.0.0
 * Project : Sandogh
 * Laravel : 12
 * Bootstrap : 5
 * --------------------------------------------------------------------------
 */

class ShebaInput {

    constructor(element) {

        this.input = element;

        this.group = this.input.closest('.input-group');

        this.feedback = this.input
            .closest('.mb-3')
            ?.querySelector('.sheba-feedback');

        this.icon = this.group
            ?.querySelector('.status-icon i');

        this.live =
            this.input.dataset.live === 'true';

        this.required =
            this.input.hasAttribute('required');

        this.init();
    }

    /**
     * Initialize
     */
    init() {

        this.normalize();

        this.bindEvents();

    }

    /**
     * Register Events
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
     * Clean Value
     */
    clean(value) {

        value = this.toEnglish(value);

        value = value.replace(/\s+/g, '');

        value = value.replace(/-/g, '');

        value = value.replace(/^IR/i, '');

        value = value.replace(/\D/g, '');

        return value.substring(0, 24);

    }

    /**
     * Normalize Value
     */
    normalize() {

        this.input.value =
            this.clean(this.input.value);

    }

    /**
     * Input Event
     */
    handleInput() {

        this.normalize();

        if (this.live) {

            this.validate();

        }

    }

    /**
     * Blur Event
     */
    handleBlur() {

        this.validate();

    }

    /**
     * Paste Event
     */
    handlePaste(event) {

        event.preventDefault();

        const text = (
            event.clipboardData ||
            window.clipboardData
        ).getData('text');

        this.input.value =
            this.clean(text);

        this.validate();

    }

    /**
     * Only Numbers
     */
    handleKeyPress(event) {

        const key = event.key;

        const allowed =
            /^[0-9۰-۹٠-٩]$/;

        if (!allowed.test(key)) {

            event.preventDefault();

        }

    }
    /**
     * Drag & Drop
     */
    handleDrop(event) {

        event.preventDefault();

        const text = event.dataTransfer.getData('text');

        this.input.value =
            this.clean(text);

        this.validate();

    }

    /**
     * Raw Value
     */
    rawValue() {

        return this.clean(this.input.value);

    }

    /**
     * Dispatch Event
     */
    dispatch(name) {

        this.input.dispatchEvent(

            new CustomEvent(name, {

                bubbles: true,

                detail: {

                    value: this.rawValue()

                }

            })

        );

    }

    /**
     * Validation
     */
    validate() {

        const value = this.rawValue();

        let valid = true;

        let message = '';

        if (this.required && value === '') {

            valid = false;

            message = 'وارد کردن شماره شبا الزامی است.';

        }
        else if (value !== '' && value.length !== 24) {

            valid = false;

            message = 'شماره شبا باید دقیقاً 24 رقم باشد.';

        }

        this.showValidation(valid, message);

        return valid;

    }

    /**
     * Show Validation
     */
    showValidation(valid, message = '') {

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

                this.input.classList.add('is-valid');

                if (this.icon) {

                    this.icon.classList.add(
                        'bi-check-circle-fill',
                        'text-success'
                    );

                }

            }

        } else {

            this.input.classList.add('is-invalid');

            if (this.feedback) {

                this.feedback.innerHTML = message;

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
 * حذف فاصله‌ها قبل از ارسال فرم
 */
document.addEventListener(
    'submit',
    function (event) {

        event.target
            .querySelectorAll('.sheba-input')
            .forEach(input => {

                input.value = input.value
                    .replace(/\s+/g, '')
                    .replace(/-/g, '')
                    .replace(/^IR/i, '')
                    .replace(/\D/g, '')
                    .substring(0, 24);

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
            .querySelectorAll('.sheba-input')
            .forEach(input => {

                new ShebaInput(input);

            });

    }
);

export default ShebaInput;

