<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => str_replace(',', '', $this->amount),
        ]);
    }

    public function rules(): array
    {
        return [
            'account_id' => [
                'required',
                'exists:accounts,id',
            ],

            'amount' => [
                'required',
                'integer',
                'min:50000',
            ],

            'payment_method' => [
                'required',
                'integer',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'account_id.required' => 'حساب الزامی است.',
            'account_id.exists' => 'حساب انتخاب شده معتبر نیست.',

            'amount.required' => 'مبلغ واریز الزامی است.',
            'amount.integer' => 'مبلغ باید عدد صحیح باشد.',
            'amount.min' => 'حداقل مبلغ واریز ۵۰ هزار ریال است.',

            'payment_method.required' => 'روش واریز را انتخاب کنید.',
            'payment_method.integer' => 'روش واریز انتخاب‌شده معتبر نیست.',

            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        ];
    }
}
