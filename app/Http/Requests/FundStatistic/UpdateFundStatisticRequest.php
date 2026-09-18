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
            'paid_loans_count' => str_replace(',', '', $this->paid_loans_count),
            'paid_loans_amount' => str_replace(',', '', $this->paid_loans_amount),
            'donations_count' => str_replace(',', '', $this->donations_count),
            'statistics_date' => $date,
        ]);
    }
    public function rules(): array
    {
        return [
            'paid_loans_count' => ['required', 'integer', 'min:0'],
            'paid_loans_amount' => ['required', 'integer', 'min:0'],
            'donations_count' => ['required', 'integer', 'min:0'],
            'statistics_date' => ['nullable', 'date'],
        ];
    }
}
