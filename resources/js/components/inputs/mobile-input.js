import {
    toEnglishNumbers,
    onlyDigits,
    maxLength,
    trim
} from '../../utils/input-helper.js';



document.addEventListener('DOMContentLoaded', () => {

    document
        .querySelectorAll('.mobile-input')
        .forEach(initMobileInput);

});

function initMobileInput(input) {

    const wrapper = input.closest('[class*="col-"]');

    const feedback = wrapper.querySelector('.mobile-feedback');

    const live = input.dataset.live === 'true';

    const icon = wrapper.querySelector('.status-icon i');

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

        if (allow.includes(e.key))
            return;

        if (!/^\d$/.test(e.key))
            e.preventDefault();

    });

    // Paste
    input.addEventListener('paste', function () {

        setTimeout(() => {

            this.value = normalize(this.value);

            if (live)
                validate(this, feedback, icon);

        }, 0);

    });

    // تایپ
    input.addEventListener('input', function () {

        this.value = normalize(this.value);

        clear(this, feedback, icon);

        if (live)
            validate(this, feedback, icon);

    });

    // خروج
    input.addEventListener('blur', function () {

        validate(this, feedback, icon);

    });

}

function normalize(value) {

    value = trim(value);

    value = toEnglishNumbers(value);

    value = onlyDigits(value);

    value = maxLength(value, 11);

    if (value.startsWith('98')) {

        value = '0' + value.substring(2);

    }

    return value;

}

function clear(input, feedback, icon) {

    input.classList.remove('is-valid');
    input.classList.remove('is-invalid');

    feedback.className = 'mobile-feedback';

    feedback.textContent = '';

    icon.className = 'bi';

}

function validate(input, feedback, icon) {

    clear(input, feedback, icon);

    const value = input.value;

    if (value === '')
        return;

    if (!value.startsWith('09')) {

        input.classList.add('is-invalid');

        feedback.classList.add('invalid-feedback');

        feedback.textContent = 'شماره موبایل باید با 09 شروع شود.';

        icon.className = 'bi bi-x-circle-fill text-danger';

        return;

    }

    if (value.length !== 11) {

        input.classList.add('is-invalid');

        feedback.classList.add('invalid-feedback');

        feedback.textContent = 'شماره موبایل باید 11 رقم باشد.';

        icon.className = 'bi bi-x-circle-fill text-danger';

        return;

    }

    input.classList.add('is-valid');

    feedback.classList.add('valid-feedback');

    feedback.textContent = 'شماره موبایل معتبر است.';

    icon.className = 'bi bi-check-circle-fill text-success';

}
