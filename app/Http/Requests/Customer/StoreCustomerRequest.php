<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
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
                ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
                ['0','1','2','3','4','5','6','7','8','9'],
                $this->account_number_suffix
            ),

            'initial_balance' => str_replace(
                ',',
                '',
                $this->initial_balance
            ),
        ]);
    }

    public function rules(): array
    {
        return [

            // -------------------------
            // اطلاعات مشتری
            // -------------------------

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

                    if ($value && !\App\Support\Iban::isValid($value)) {
                        $fail('شماره شبا معتبر نیست.');
                    }

                },
            ],


            // -------------------------
            // حساب مشتری
            // -------------------------

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

            'customer_code.required' =>
                'کد مشتری الزامی است.',

            'customer_code.unique' =>
                'این کد مشتری قبلاً ثبت شده است.',


            'first_name.required' =>
                'نام الزامی است.',

            'last_name.required' =>
                'نام خانوادگی الزامی است.',


            'national_code.required' =>
                'کد ملی الزامی است.',

            'national_code.digits' =>
                'کد ملی باید ۱۰ رقم باشد.',

            'national_code.unique' =>
                'این کد ملی قبلاً ثبت شده است.',


            'mobile.required' =>
                'شماره موبایل الزامی است.',

            'mobile.digits' =>
                'شماره موبایل باید ۱۱ رقم باشد.',

            'mobile.unique' =>
                'این شماره موبایل قبلاً ثبت شده است.',


            'account_type.required' =>
                'نوع حساب را انتخاب کنید.',

            'account_type.in' =>
                'نوع حساب انتخاب‌شده معتبر نیست.',


            'account_number.required' =>
                'شماره حساب الزامی است.',

            'account_number.unique' =>
                'این شماره حساب قبلاً ثبت شده است.',


            'balance.required' =>
                'موجودی اولیه الزامی است.',

            'balance.integer' =>
                'موجودی اولیه باید عدد صحیح باشد.',

            'balance.min' =>
                'موجودی اولیه نمی‌تواند منفی باشد.',
        ];
    }
}
