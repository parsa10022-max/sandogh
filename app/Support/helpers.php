<?php

if (! function_exists('fa_number')) {
    /**
     * Convert English digits to Persian digits.
     */
    function fa_number($value): string
    {
        if ($value === null) {
            return '';
        }

        return strtr((string) $value, [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ]);
    }
}

if (! function_exists('en_number')) {
    /**
     * Convert Persian/Arabic digits to English digits.
     */
    function en_number($value): string
    {
        if ($value === null) {
            return '';
        }

        return strtr((string) $value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',

            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }
}

if (! function_exists('fa_money')) {
    /**
     * Format an amount with Persian digits and thousands separator.
     *
     * Example:
     * 12500000 => ۱۲٬۵۰۰٬۰۰۰
     */
    function fa_money($value): string
    {
        if ($value === null || $value === '') {
            return '۰';
        }

        $value = en_number($value);

        $negative = str_starts_with($value, '-');

        if ($negative) {
            $value = substr($value, 1);
        }

        $value = preg_replace('/[^\d]/', '', $value);

        if ($value === '') {
            return '۰';
        }

        $formatted = number_format((int) $value, 0, '', '٬');

        if ($negative) {
            $formatted = '-' . $formatted;
        }

        return fa_number($formatted);
    }
}

if (! function_exists('fa_money_with_unit')) {
    /**
     * Format money with Rial unit.
     *
     * Example:
     * 12500000 => ۱۲٬۵۰۰٬۰۰۰ ریال
     */
    function fa_money_with_unit($value): string
    {
        return fa_money($value) . ' ریال';
    }
}

if (! function_exists('clean_money')) {
    /**
     * Convert a Persian/Arabic formatted money value
     * into a clean English integer for backend processing.
     *
     * Example:
     * ۱۲٬۵۰۰٬۰۰۰ => 12500000
     */
    function clean_money($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = en_number($value);

        $value = str_replace([
            ',',
            '٬',
            '،',
            ' ',
            'ریال',
        ], '', $value);

        $value = preg_replace('/[^\d-]/', '', $value);

        if ($value === '' || $value === '-') {
            return null;
        }

        return (int) $value;
    }
}
