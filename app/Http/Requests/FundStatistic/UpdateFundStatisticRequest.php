<?php

namespace App\Http\Requests\FundStatistic;

use Illuminate\Foundation\Http\FormRequest;
use Morilog\Jalali\Jalalian;

class UpdateFundStatisticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $date = null;

        if ($this->filled('statistics_date')) {
            $date = Jalalian::fromFormat(
                'Y/m/d',
                $this->statistics_date
            )->toCarbon()->format('Y-m-d');
        }

        $this->merge([
            'paid_loans_count' => clean_money($this->paid_loans_count),
            'paid_loans_amount' => clean_money($this->paid_loans_amount),
            'donations_count' => clean_money($this->donations_count),
            'statistics_date' => $date,
        ]);
    }

    public function rules(): array
    {
        return [
            'paid_loans_count' => [
                'required',
                'integer',
                'min:0',
            ],

            'paid_loans_amount' => [
                'required',
                'integer',
                'min:0',
            ],

            'donations_count' => [
                'required',
                'integer',
                'min:0',
            ],

            'statistics_date' => [
                'nullable',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'paid_loans_count.required' =>
                'تعداد وام‌های پرداخت‌شده الزامی است.',

            'paid_loans_count.integer' =>
                'تعداد وام‌های پرداخت‌شده باید عدد صحیح باشد.',

            'paid_loans_count.min' =>
                'تعداد وام‌های پرداخت‌شده نمی‌تواند منفی باشد.',

            'paid_loans_amount.required' =>
                'مبلغ وام‌های پرداخت‌شده الزامی است.',

            'paid_loans_amount.integer' =>
                'مبلغ وام‌های پرداخت‌شده باید عدد صحیح باشد.',

            'paid_loans_amount.min' =>
                'مبلغ وام‌های پرداخت‌شده نمی‌تواند منفی باشد.',

            'donations_count.required' =>
                'تعداد کمک‌های مالی الزامی است.',

            'donations_count.integer' =>
                'تعداد کمک‌های مالی باید عدد صحیح باشد.',

            'donations_count.min' =>
                'تعداد کمک‌های مالی نمی‌تواند منفی باشد.',

            'statistics_date.date' =>
                'تاریخ واردشده معتبر نیست.',
        ];
    }
}
