document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('loan-form');

    if (!form) return;

    const previewCard = document.getElementById('loan-preview-card');

    /*
    |--------------------------------------------------------------------------
    | پیش شماره وام
    |--------------------------------------------------------------------------
    */

    const loanType = form.elements.loan_type_id;
    const prefix = document.getElementById('loan-prefix');

    function updateLoanPrefix() {

        if (!loanType || !prefix) return;

        const option = loanType.options[loanType.selectedIndex];

        prefix.textContent = option.dataset.prefix ?? '----';

    }

    updateLoanPrefix();

    loanType?.addEventListener(
        'change',
        updateLoanPrefix
    );

    /*
    |--------------------------------------------------------------------------
    | محاسبه وام
    |--------------------------------------------------------------------------
    */

    const fields = [
        'loan_amount',
        'installment_count',
        'installment_interval',
        'start_date'
    ];

    fields.forEach(name => {

        const element = form.elements[name];

        if (!element) return;

        element.addEventListener(
            'change',
            calculateLoan
        );

        element.addEventListener(
            'keyup',
            calculateLoan
        );

    });

    async function calculateLoan() {

        const loanAmount =
            form.elements.loan_amount.value.replace(/,/g, '');

        const installmentCount =
            form.elements.installment_count.value;

        const interval =
            form.elements.installment_interval.value;

        const startDate =
            form.elements.start_date.value;

        if (
            !loanAmount ||
            !installmentCount ||
            !interval ||
            !startDate
        ) {

            hidePreview();

            return;

        }
        try {

            const response = await fetch(
                form.dataset.calculateUrl,
                {

                    method: 'POST',

                    headers: {

                        'Content-Type': 'application/json',

                        'Accept': 'application/json',

                        'X-CSRF-TOKEN':
                        document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,

                    },

                    body: JSON.stringify({

                        loan_amount: loanAmount,

                        installment_count: installmentCount,

                        installment_interval: interval,

                        start_date: startDate,

                    }),

                }
            );

            const result = await response.json();

            if (!result.success) {

                hidePreview();

                return;

            }

            renderPreview(result.data);

        }

        catch (error) {

            console.error(error);

            hidePreview();

        }

    }

    /*
    |--------------------------------------------------------------------------
    | نمایش نتیجه
    |--------------------------------------------------------------------------
    */

    function renderPreview(data) {

        document.getElementById('preview-start-date').textContent =
            data.start_date;

        document.getElementById('preview-first-date').textContent =
            data.first_due_date;

        document.getElementById('preview-last-date').textContent =
            data.last_due_date;

        document.getElementById('preview-count').textContent =
            data.installment_count;

        document.getElementById('preview-installment').textContent =
            data.installment_amount + ' ریال';
        previewCard.classList.remove('d-none');

        /*
        |--------------------------------------------------------------------------
        | دکمه برنامه اقساط
        |--------------------------------------------------------------------------
        */

        const scheduleButton =
            document.getElementById('show-schedule');

        if (scheduleButton) {

            scheduleButton.disabled = false;

            scheduleButton.dataset.schedule =
                JSON.stringify(data.schedule);

        }

    }

    /*
    |--------------------------------------------------------------------------
    | مخفی کردن کارت
    |--------------------------------------------------------------------------
    */

    function hidePreview() {

        previewCard.classList.add('d-none');

        const scheduleButton =
            document.getElementById('show-schedule');

        if (scheduleButton) {

            scheduleButton.disabled = true;

            scheduleButton.removeAttribute('data-schedule');

        }

    }

    /*
    |--------------------------------------------------------------------------
    | اگر فرم ویرایش باشد، پیش‌نمایش را همان ابتدا نمایش بده
    |--------------------------------------------------------------------------
    */

    calculateLoan();

});
