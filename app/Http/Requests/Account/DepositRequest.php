<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
                'min:500000',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
            'payment_method' => [
                'required',
                'integer',
            ],
        ];
    }


    public function messages(): array
    {
        return [
            'amount.required' => 'مبلغ واریز الزامی است.',
            'amount.integer' => 'مبلغ باید عدد باشد.',
            'amount.min' => 'حداقل مبلغ واریز ۵۰ هزار ریال است.',

            'account_id.required' => 'حساب الزامی است.',
            'account_id.exists' => 'حساب انتخاب شده معتبر نیست.',
            'payment_method.required' => 'روش واریز را انتخاب کنید.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        ];
    }
}
