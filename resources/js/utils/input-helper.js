/**
 * تبدیل اعداد فارسی و عربی به انگلیسی
 */
export function toEnglishNumbers(value) {

    return value
        .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
        .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));

}

/**
 * حذف تمام کاراکترهای غیرعددی
 */
export function onlyDigits(value) {

    return value.replace(/\D/g, '');

}

/**
 * محدود کردن طول
 */
export function maxLength(value, length) {

    return value.substring(0, length);

}

/**
 * حذف فاصله ابتدا و انتها
 */
export function trim(value) {

    return value.trim();

}
