<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'balance' => str_replace(',', '', $this->balance),
        ]);
    }

    public function rules(): array
    {
        return [
            'account_number' => [
                'required',
                'string',
                'max:20',
                'unique:accounts,account_number',
            ],

            'account_type' => [
                'required',
                'integer',
                'in:1,2',
            ],

            'balance' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required' => 'شماره حساب الزامی است.',
            'account_number.unique' => 'این شماره حساب قبلاً ثبت شده است.',

            'account_type.required' => 'نوع حساب را انتخاب کنید.',
            'account_type.in' => 'نوع حساب انتخاب‌شده معتبر نیست.',

            'balance.required' => 'مبلغ الزامی است.',
            'balance.integer' => 'مبلغ باید به صورت عدد صحیح وارد شود.',
            'balance.min' => 'مبلغ نمی‌تواند منفی باشد.',
        ];
    }
}
