import {
    toEnglishNumbers,
    onlyDigits,
    maxLength,
    trim
} from '../../utils/input-helper.js';



document.addEventListener('DOMContentLoaded', () => {

    document
        .querySelectorAll('.national-code-input')
        .forEach(initNationalCodeInput);

});

function initNationalCodeInput(input) {

    const wrapper = input.closest('[class*="col-"]');

    if (!wrapper) return;

    const feedback = wrapper.querySelector('.national-code-feedback');
    const icon = wrapper.querySelector('.status-icon i');

    const live = input.dataset.live === 'true';

    // جلوگیری از ورود حروف
    input.addEventListener('keydown', function (e) {

        const allow = [
            'Backspace',
            'Delete',
            'ArrowLeft',
            'ArrowRight',
            'Tab',
            'Home',
            'End'
        ];

        if (allow.includes(e.key)) return;

        if (!/^\d$/.test(e.key)) {
            e.preventDefault();
        }

    });

    // Paste
    input.addEventListener('paste', function () {

        setTimeout(() => {

            this.value = normalize(this.value);

            if (live) {
                validate(this, feedback, icon);
            }

        }, 0);

    });

    // هنگام تایپ
    input.addEventListener('input', function () {

        this.value = normalize(this.value);

        clear(this, feedback, icon);

        if (live) {
            validate(this, feedback, icon);
        }

    });

    // هنگام خروج
    input.addEventListener('blur', function () {

        validate(this, feedback, icon);

    });

}

/**
 * تبدیل به فرمت استاندارد
 */
function normalize(value) {

    value = trim(value);

    value = toEnglishNumbers(value);

    value = onlyDigits(value);

    value = maxLength(value, 10);

    return value;

}

/**
 * پاک کردن وضعیت
 */
function clear(input, feedback, icon) {

    input.classList.remove('is-valid');
    input.classList.remove('is-invalid');

    if (feedback) {
        feedback.className = 'national-code-feedback';
        feedback.textContent = '';
    }

    if (icon) {
        icon.className = 'bi';
    }

}

/**
 * اعتبارسنجی
 */
function validate(input, feedback, icon) {

    clear(input, feedback, icon);

    const value = input.value;

    if (value === '') {
        return;
    }

    if (value.length !== 10) {

        input.classList.add('is-invalid');

        if (feedback) {
            feedback.classList.add('invalid-feedback');
            feedback.textContent = 'کد ملی باید دقیقاً 10 رقم باشد.';
        }

        if (icon) {
            icon.className = 'bi bi-x-circle-fill text-danger';
        }

        return;

    }

    if (!isValidNationalCode(value)) {

        input.classList.add('is-invalid');

        if (feedback) {
            feedback.classList.add('invalid-feedback');
            feedback.textContent = 'کد ملی معتبر نیست.';
        }

        if (icon) {
            icon.className = 'bi bi-x-circle-fill text-danger';
        }

        return;

    }

    input.classList.add('is-valid');

    if (feedback) {
        feedback.classList.add('valid-feedback');
        feedback.textContent = 'کد ملی معتبر است.';
    }

    if (icon) {
        icon.className = 'bi bi-check-circle-fill text-success';
    }

}

/**
 * الگوریتم رسمی اعتبارسنجی کد ملی
 */
function isValidNationalCode(code) {

    if (!/^\d{10}$/.test(code)) {
        return false;
    }

    if (/^(\d)\1{9}$/.test(code)) {
        return false;
    }

    let sum = 0;

    for (let i = 0; i < 9; i++) {

        sum += parseInt(code[i], 10) * (10 - i);

    }

    const remainder = sum % 11;

    const checkDigit = parseInt(code[9], 10);

    if (remainder < 2) {
        return checkDigit === remainder;
    }

    return checkDigit === (11 - remainder);

}
