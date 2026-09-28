<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use App\Support\Iban;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'iban' => Iban::normalize($this->iban),

            'account_number_suffix' => str_replace(
                ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                $this->account_number_suffix
            ),

            'initial_balance' => clean_money($this->initial_balance),
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_code' => [
                'required',
                'integer',
                'unique:customers,customer_code',
            ],

            'first_name' => [
                'required',
                'string',
                'max:50',
            ],

            'last_name' => [
                'required',
                'string',
                'max:50',
            ],

            'father_name' => [
                'nullable',
                'string',
                'max:50',
            ],

            'national_code' => [
                'required',
                'digits:10',
                'unique:customers,national_code',
            ],

            'mobile' => [
                'required',
                'digits:11',
                'unique:customers,mobile',
            ],

            'mobile_second' => [
                'nullable',
                'digits:11',
            ],

            'iban' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && !Iban::isValid($value)) {
                        $fail('شماره شبا معتبر نیست.');
                    }
                },
            ],

            'account_type' => [
                'required',
                'integer',
                'in:1,2',
            ],

            'account_number_suffix' => [
                'required',
                'digits_between:1,16',
            ],

            'initial_balance' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_code.required' => 'کد مشتری الزامی است.',
            'customer_code.integer' => 'کد مشتری باید به صورت عدد صحیح باشد.',
            'customer_code.unique' => 'این کد مشتری قبلاً ثبت شده است.',

            'first_name.required' => 'نام الزامی است.',
            'first_name.string' => 'نام باید به صورت متن وارد شود.',
            'first_name.max' => 'نام نمی‌تواند بیشتر از ۵۰ کاراکتر باشد.',

            'last_name.required' => 'نام خانوادگی الزامی است.',
            'last_name.string' => 'نام خانوادگی باید به صورت متن وارد شود.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیشتر از ۵۰ کاراکتر باشد.',

            'father_name.string' => 'نام پدر باید به صورت متن وارد شود.',
            'father_name.max' => 'نام پدر نمی‌تواند بیشتر از ۵۰ کاراکتر باشد.',

            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.digits' => 'کد ملی باید ۱۰ رقم باشد.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',

            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',

            'mobile_second.digits' => 'شماره موبایل دوم باید ۱۱ رقم باشد.',

            'iban.required' => 'شماره شبا الزامی است.',

            'account_type.required' => 'نوع حساب را انتخاب کنید.',
            'account_type.integer' => 'نوع حساب انتخاب‌شده معتبر نیست.',
            'account_type.in' => 'نوع حساب انتخاب‌شده معتبر نیست.',

            'account_number_suffix.required' => 'شماره حساب الزامی است.',
            'account_number_suffix.digits_between' =>
                'بخش شماره حساب باید بین ۱ تا ۱۶ رقم باشد.',

            'initial_balance.required' => 'موجودی اولیه الزامی است.',
            'initial_balance.integer' => 'موجودی اولیه باید عدد صحیح باشد.',
            'initial_balance.min' => 'موجودی اولیه نمی‌تواند منفی باشد.',
        ];
    }
}
