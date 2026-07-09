/**
 * --------------------------------------------------------------------------
 * Date Input Component
 * --------------------------------------------------------------------------
 * Version : 1.0.0
 * Project : Sandogh
 * Laravel : 12
 * Bootstrap : 5
 * --------------------------------------------------------------------------
 */

import 'persian-datepicker-element/dist/persian-datepicker-element.min.esm.js';

class DateInput {

    /**
     * Constructor
     */
    constructor(element) {

        // Date Picker
        this.picker = element;

        // Hidden Input
        this.hidden = document.getElementById(
            element.id.replace('_picker', '')
        );

        // Input Group
        this.group =
            this.picker.closest('.input-group');

        // Feedback
        this.feedback =
            this.group
                ?.parentElement
                ?.querySelector('.date-feedback');

        // Status Icon
        this.icon =
            this.group
                ?.querySelector('.status-icon i');

        // Live Validation
        this.live =
            this.picker.dataset.live === 'true';

        // Required
        this.required =
            this.picker.hasAttribute('required');

        // Disabled
        this.disabled =
            this.picker.hasAttribute('disabled');

        this.init();

    }

    /**
     * Initialize
     */
    init() {

        this.bindEvents();

    }

    /**
     * Register Events
     */
    bindEvents() {

        /**
         * Date Changed
         */
        this.picker.addEventListener(

            'change',

            this.handleChange.bind(this)

        );

        /**
         * Blur
         */
        this.picker.addEventListener(

            'blur',

            this.handleBlur.bind(this)

        );

    }

    /**
     * Date Changed
     */
    handleChange(event) {

        const detail = event.detail || {};

        this.updateHidden(detail);

        if (this.live) {

            this.validate();

        }

        this.dispatch(
            'date:change',
            detail
        );

    }

    /**
     * Blur Event
     */
    handleBlur() {

        this.validate();

    }

    /**
     * Update Hidden Input
     */
    updateHidden(detail) {

        if (!this.hidden) {

            return;

        }

        this.hidden.value =
            detail?.isoString ?? '';

    }

    /**
     * Raw Value
     */
    rawValue() {

        return this.hidden
            ? this.hidden.value
            : '';

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
            message = 'انتخاب تاریخ الزامی است.';

        }

        this.showValidation(valid, message);
        this.picker.classList.remove(
            'is-valid',
            'is-invalid'
        );
        return valid;

    }

    /**
     * Show Validation
     */
    showValidation(valid, message = '') {

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

                if (this.icon) {

                    this.icon.classList.add(
                        'bi-check-circle-fill',
                        'text-success'
                    );

                }

            }

        } else {

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

    /**
     * Dispatch Custom Event
     */
    dispatch(name, detail = {}) {

        this.picker.dispatchEvent(

            new CustomEvent(name, {

                bubbles: true,

                detail

            })

        );

    }

    /**
     * Set Date
     */
    setValue(year, month, day) {

        if (typeof this.picker.setValue === 'function') {

            this.picker.setValue(
                year,
                month,
                day
            );

        }

    }

    /**
     * Get Value
     */
    getValue() {

        if (typeof this.picker.getValue === 'function') {

            return this.picker.getValue();

        }

        return null;

    }

    /**
     * Clear
     */
    clear() {

        if (typeof this.picker.clear === 'function') {

            this.picker.clear();

        }

        if (this.hidden) {

            this.hidden.value = '';

        }

        this.showValidation(true);

    }

}

/**
 * Auto Initialize
 */
document.addEventListener(

    'DOMContentLoaded',

    () => {

        document

            .querySelectorAll('persian-datepicker-element')

            .forEach(element => {

                new DateInput(element);

            });

    }

);

export default DateInput;

