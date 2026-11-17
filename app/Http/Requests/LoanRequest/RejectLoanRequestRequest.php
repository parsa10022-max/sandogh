<?php

namespace App\Http\Requests\LoanRequest;

use Illuminate\Foundation\Http\FormRequest;
use Morilog\Jalali\Jalalian;
use Throwable;

class RejectLoanRequestRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $date = $this->input('next_review_date');

        if (is_string($date)) {
            $date = strtr($date, [
                '۰' => '0', '۱' => '1', '۲' => '2',
                '۳' => '3', '۴' => '4', '۵' => '5',
                '۶' => '6', '۷' => '7', '۸' => '8',
                '۹' => '9',
                '٠' => '0', '١' => '1', '٢' => '2',
                '٣' => '3', '٤' => '4', '٥' => '5',
                '٦' => '6', '٧' => '7', '٨' => '8',
                '٩' => '9',
            ]);
        }

        $this->merge([
            'next_review_date' => $date,
        ]);
    }

    private function isValidJalaliDate(mixed $date): bool
    {
        if (
            ! is_string($date) ||
            ! preg_match('/\A\d{4}\/\d{2}\/\d{2}\z/', $date)
        ) {
            return false;
        }

        try {
            $parsed = Jalalian::fromFormat('Y/m/d', $date);

            return $parsed->format('Y/m/d') === $date;
        } catch (Throwable) {
            return false;
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review_note' => [
                'bail',
                'required',
                'string',
                'max:1000',
            ],

            'next_review_date' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidJalaliDate($value)) {
                        $fail('تاریخ بررسی بعدی باید یک تاریخ شمسی معتبر باشد.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'review_note.required' =>
                'پیام رد درخواست را وارد کنید.',
            'review_note.string' =>
                'پیام رد درخواست باید به صورت متن وارد شود.',
            'review_note.max' =>
                'پیام رد درخواست نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',

            'next_review_date.string' =>
                'تاریخ بررسی بعدی معتبر نیست.',
        ];
    }
}
