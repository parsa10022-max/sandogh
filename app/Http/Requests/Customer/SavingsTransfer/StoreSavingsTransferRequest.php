<?php

namespace App\Http\Requests\Customer\SavingsTransfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => clean_money($this->amount),
        ]);
    }

    public function rules(): array
    {
        return [
            'membership_number' => [
                'required',
                'integer',
                'exists:customers,membership_number',
            ],

            'amount' => [
                'required',
                'integer',
                'min:1000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'membership_number' => 'شماره عضویت',
            'amount' => 'مبلغ',
        ];
    }

    public function messages(): array
    {
        return [
            'membership_number.required' => 'شماره عضویت الزامی است.',
            'membership_number.integer' => 'شماره عضویت باید به صورت عدد صحیح باشد.',
            'membership_number.exists' => 'شماره عضویت واردشده معتبر نیست.',

            'amount.required' => 'مبلغ انتقال الزامی است.',
            'amount.integer' => 'مبلغ انتقال باید به صورت عدد صحیح باشد.',
            'amount.min' => 'حداقل مبلغ انتقال ۱٬۰۰۰ ریال است.',
        ];
    }
}
