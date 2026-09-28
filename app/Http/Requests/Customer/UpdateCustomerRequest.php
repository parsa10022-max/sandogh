<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Support\Iban;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'customer_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'customer_code')->ignore($customer),
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'father_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'national_code' => [
                'required',
                'digits:10',
                Rule::unique('customers', 'national_code')->ignore($customer),
            ],

            'mobile' => [
                'required',
                'digits:11',
                Rule::unique('customers', 'mobile')->ignore($customer),
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
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'iban' => Iban::normalize($this->iban),
        ]);
    }

    public function messages(): array
    {
        return [
            'customer_code.required' => 'کد مشتری الزامی است.',
            'customer_code.string' => 'کد مشتری باید به صورت متن وارد شود.',
            'customer_code.max' => 'کد مشتری نمی‌تواند بیشتر از ۲۰ کاراکتر باشد.',
            'customer_code.unique' => 'این کد مشتری قبلاً ثبت شده است.',

            'first_name.required' => 'نام الزامی است.',
            'first_name.string' => 'نام باید به صورت متن وارد شود.',
            'first_name.max' => 'نام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'last_name.required' => 'نام خانوادگی الزامی است.',
            'last_name.string' => 'نام خانوادگی باید به صورت متن وارد شود.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'father_name.string' => 'نام پدر باید به صورت متن وارد شود.',
            'father_name.max' => 'نام پدر نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'national_code.required' => 'کد ملی الزامی است.',
            'national_code.digits' => 'کد ملی باید ۱۰ رقم باشد.',
            'national_code.unique' => 'این کد ملی قبلاً ثبت شده است.',

            'mobile.required' => 'شماره موبایل الزامی است.',
            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',
            'mobile.unique' => 'این شماره موبایل قبلاً ثبت شده است.',

            'mobile_second.digits' => 'شماره موبایل دوم باید ۱۱ رقم باشد.',

            'iban.required' => 'شماره شبا الزامی است.',
        ];
    }
}
