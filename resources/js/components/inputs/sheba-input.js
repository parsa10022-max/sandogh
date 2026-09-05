/**
 * --------------------------------------------------------------------------
 * Iban Input Component
 * --------------------------------------------------------------------------
 * Version : 1.2.0
 * Project : Sandogh
 * Laravel : 12
 * Bootstrap : 5
 * RTL
 * --------------------------------------------------------------------------
 */


console.log(
    'IBAN COMPONENT LOADED'
);


class IbanInput {

    constructor(element) {

        this.input =
            element;


        /*
         * ساختار جدید فرم
         *
         * .customer-form-field
         *      ├── .sheba-field
         *      │     ├── .sheba-bank
         *      │     │     └── .sheba-bank__text
         *      │     ├── .sheba-input
         *      │     └── .sheba-prefix
         *      ├── .status-icon
         *      │     └── i
         *      └── .sheba-feedback
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
         * Feedback
         */
        this.feedback =
            this.field?.querySelector(
                '.sheba-feedback'
            );


        /*
         * Status Icon
         */
        this.icon =
            this.field?.querySelector(
                '.status-icon i'
            );


        /*
         * نام بانک
         *
         * ساختار جدید:
         * .sheba-bank__text
         */
        this.bankName =
            this.field?.querySelector(
                '.sheba-bank__text'
            );


        /*
         * Live Validation
         */
        this.live =
            this.input.dataset.live === 'true';


        /*
         * Required
         */
        this.required =
            this.input.hasAttribute(
                'required'
            );


        this.init();

    }


    /**
     * Initialize
     */
    init() {

        /*
         * مقدار اولیه
         */
        this.normalize();


        /*
         * تشخیص بانک
         */
        this.detectBank();


        /*
         * اتصال Eventها
         */
        this.bindEvents();


        /*
         * اگر مقدار اولیه کامل است،
         * وضعیت آن را نشان بده.
         */
        if (
            this.rawValue() !== ''
        ) {

            this.validate();

        }

    }


    /**
     * Register Events
     */
    bindEvents() {

        /*
         * Input
         */
        this.input.addEventListener(
            'input',
            this.handleInput.bind(this)
        );


        /*
         * Blur
         */
        this.input.addEventListener(
            'blur',
            this.handleBlur.bind(this)
        );


        /*
         * Paste
         */
        this.input.addEventListener(
            'paste',
            this.handlePaste.bind(this)
        );


        /*
         * Keypress
         */
        this.input.addEventListener(
            'keypress',
            this.handleKeyPress.bind(this)
        );


        /*
         * Drop
         */
        this.input.addEventListener(
            'drop',
            this.handleDrop.bind(this)
        );

    }


    /**
     * Persian & Arabic Numbers → English
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
     * Clean Value
     */
    clean(value) {

        value =
            this.toEnglish(
                value.toString()
            );


        /*
         * حذف فاصله
         */
        value =
            value.replace(
                /\s+/g,
                ''
            );


        /*
         * حذف -
         */
        value =
            value.replace(
                /-/g,
                ''
            );


        /*
         * حذف IR
         */
        value =
            value.replace(
                /^IR/i,
                ''
            );


        /*
         * فقط اعداد
         */
        value =
            value.replace(
                /\D/g,
                ''
            );


        /*
         * حداکثر 24 رقم
         */
        return value.substring(
            0,
            24
        );

    }


    /**
     * Normalize
     */
    normalize() {

        this.input.value =
            this.clean(
                this.input.value
            );

    }


    /**
     * Input
     */
    handleInput() {

        this.normalize();


        /*
         * بانک را بلافاصله تشخیص بده
         */
        this.detectBank();


        /*
         * اگر live فعال است
         */
        if (this.live) {

            this.validate();

        }

    }


    /**
     * Blur
     */
    handleBlur() {

        this.detectBank();


        this.validate();

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
            this.clean(text);


        this.detectBank();


        this.validate();

    }


    /**
     * Keyboard
     */
    handleKeyPress(event) {

        const key =
            event.key;


        const allowed =
            /^[0-9۰-۹٠-٩]$/;


        /*
         * فقط عدد
         */
        if (!allowed.test(key)) {

            event.preventDefault();

        }

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
            this.clean(text);


        this.detectBank();


        this.validate();

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
     * Detect Bank
     */
    detectBank() {

        const value =
            this.rawValue();


        /*
         * اگر المنت نام بانک وجود نداشته باشد،
         * فقط از تابع خارج شو.
         */
        if (!this.bankName) {

            console.warn(
                'IBAN: .sheba-bank__text not found'
            );

            return;

        }


        /*
         * پاک کردن نام قبلی
         */
        this.bankName.textContent =
            'بانک';


        /*
         * هنوز حداقل اطلاعات لازم برای
         * تشخیص بانک وارد نشده.
         *
         * ساختار:
         * IR + 2 رقم کنترلی + 3 رقم کد بانک
         */
        if (value.length < 5) {

            return;

        }


        /*
         * سه رقم کد بانک
         *
         * value:
         * 820540102680020817909002
         *
         * index 2 تا 4:
         * 054
         */
        const bankCode =
            value.substring(
                2,
                5
            );


        const banks = {

            '010': 'بانک مرکزی',

            '011': 'بانک صنعت و معدن',

            '012': 'بانک ملت',

            '013': 'بانک رفاه کارگران',

            '014': 'بانک مسکن',

            '015': 'بانک سپه',

            '016': 'بانک کشاورزی',

            '017': 'بانک ملی ایران',

            '018': 'بانک تجارت',

            '019': 'بانک صادرات ایران',

            '020': 'بانک توسعه صادرات',

            '021': 'پست بانک ایران',

            '022': 'بانک توسعه تعاون',

            '051': 'موسسه اعتباری توسعه',

            '052': 'بانک قوامین',

            '053': 'بانک کارآفرین',

            '054': 'بانک پارسیان',

            '055': 'بانک اقتصاد نوین',

            '056': 'بانک سامان',

            '057': 'بانک پاسارگاد',

            '058': 'بانک سرمایه',

            '059': 'بانک سینا',

            '060': 'بانک مهر اقتصاد',

            '061': 'بانک شهر',

            '062': 'بانک آینده',

            '063': 'بانک انصار',

            '064': 'بانک گردشگری',

            '065': 'بانک حکمت ایرانیان',

            '066': 'بانک دی',

            '069': 'بانک ایران زمین',

            '070': 'بانک رسالت',

            '073': 'بانک تعاون اسلامی',

            '075': 'بانک موسسه ملل',

            '078': 'بانک خاورمیانه',

            '080': 'بانک نور',

            '090': 'بانک مرکزی',

            '095': 'بانک خاورمیانه',

        };


        const name =
            banks[bankCode];


        if (name) {

            this.bankName.textContent =
                name;

        } else {

            this.bankName.textContent =
                'بانک نامشخص';

        }

    }


    /**
     * Validate
     */
    validate() {

        const value =
            this.rawValue();


        let valid = true;


        let message = '';


        /*
         * Required
         */
        if (
            this.required &&
            value === ''
        ) {

            valid = false;


            message =
                'وارد کردن شماره شبا الزامی است.';

        }


        /*
         * Length
         */
        else if (
            value !== '' &&
            value.length !== 24
        ) {

            valid = false;


            message =
                'شماره شبا باید دقیقاً 24 رقم باشد.';

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

        /*
         * حذف وضعیت قبلی
         */
        this.input.classList.remove(
            'is-valid',
            'is-invalid'
        );


        /*
         * Feedback
         */
        if (this.feedback) {

            this.feedback.classList.remove(
                'valid-feedback',
                'invalid-feedback',
                'd-block'
            );


            this.feedback.innerHTML =
                '';

        }


        /*
         * Icon
         */
        if (this.icon) {

            this.icon.className =
                'bi';

        }


        /*
         * معتبر
         */
        if (valid) {

            if (
                this.rawValue() !== ''
            ) {

                this.input.classList.add(
                    'is-valid'
                );


                /*
                 * تیک سبز
                 */
                if (this.icon) {

                    this.icon.className =
                        'bi bi-check-circle-fill text-success';

                }


                /*
                 * پیام موفقیت
                 */
                if (this.feedback) {

                    this.feedback.classList.add(
                        'valid-feedback'
                    );


                    this.feedback.textContent =
                        'شماره شبا معتبر است.';

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


        /*
         * پیام خطا
         */
        if (this.feedback) {

            this.feedback.innerHTML =
                message;


            this.feedback.classList.add(
                'invalid-feedback',
                'd-block'
            );

        }


        /*
         * ضربدر قرمز
         */
        if (this.icon) {

            this.icon.className =
                'bi bi-x-circle-fill text-danger';

        }

    }

}


/**
 * --------------------------------------------------------------------------
 * حذف فرمت قبل از Submit
 * --------------------------------------------------------------------------
 */

document.addEventListener(
    'submit',
    function (event) {

        event.target
            .querySelectorAll(
                '.sheba-input'
            )
            .forEach(input => {

                input.value =
                    input.value
                        .replace(
                            /\s+/g,
                            ''
                        )
                        .replace(
                            /-/g,
                            ''
                        )
                        .replace(
                            /^IR/i,
                            ''
                        )
                        .replace(
                            /\D/g,
                            ''
                        )
                        .substring(
                            0,
                            24
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
                '.sheba-input'
            )
            .forEach(input => {

                /*
                 * جلوگیری از initialize دوباره
                 */
                if (
                    input.dataset.ibanInitialized === 'true'
                ) {

                    return;

                }


                input.dataset.ibanInitialized =
                    'true';


                new IbanInput(input);

            });

    }
);


export default IbanInput;
