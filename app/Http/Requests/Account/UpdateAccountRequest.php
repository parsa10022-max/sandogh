<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $account = $this->route('account');

        return [
            'account_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('accounts', 'account_number')
                    ->ignore($account?->id),
            ],

            'account_type' => [
                'required',
                'integer',
                'in:1,2',
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
        ];
    }
}
