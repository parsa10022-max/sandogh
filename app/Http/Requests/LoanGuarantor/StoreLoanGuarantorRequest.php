<?php

namespace App\Http\Requests\LoanGuarantor;

use App\Enums\GuaranteeType;
use App\Enums\GuarantorType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreLoanGuarantorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'loan_id' => [
                'required',
                'exists:loans,id',
            ],

            'guarantor_order' => [
                'required',
                'integer',
                'in:1,2',
            ],

            'guarantor_type' => [
                'required',
                new Enum(GuarantorType::class),
            ],

            'customer_id' => [
                'nullable',
                'exists:customers,id',
            ],

            'first_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'national_code' => [
                'nullable',
                'digits:10',
            ],

            'mobile' => [
                'nullable',
                'digits:11',
            ],

            'guarantee_type' => [
                'required',
                new Enum(GuaranteeType::class),
            ],

            'guarantee_amount' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'guarantee_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'guarantee_account_number' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
            |--------------------------------------------------------------------------
            | ضامن اول
            |--------------------------------------------------------------------------
            |
            | ضامن اول حتماً باید عضو صندوق باشد.
            |
            */

            if (
                $this->guarantor_order == 1 &&
                $this->guarantor_type !== GuarantorType::CUSTOMER->value
            ) {
                $validator->errors()->add(
                    'guarantor_type',
                    'ضامن اول باید عضو صندوق باشد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | عضو صندوق
            |--------------------------------------------------------------------------
            */

            if (
                $this->guarantor_type === GuarantorType::CUSTOMER->value &&
                empty($this->customer_id)
            ) {
                $validator->errors()->add(
                    'customer_id',
                    'کد مشتری ضامن الزامی است.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | خود وام‌گیرنده
            |--------------------------------------------------------------------------
            |
            | فقط ضامن دوم و فقط با چک صیادی.
            |
            */

            if (
                $this->guarantor_type === GuarantorType::BORROWER->value
            ) {
                if ($this->guarantor_order != 2) {
                    $validator->errors()->add(
                        'guarantor_order',
                        'خود وام‌گیرنده فقط می‌تواند به عنوان ضامن دوم ثبت شود.'
                    );
                }

                if (
                    $this->guarantee_type !==
                    GuaranteeType::CHECK->value
                ) {
                    $validator->errors()->add(
                        'guarantee_type',
                        'خود وام‌گیرنده فقط با چک صیادی می‌تواند به عنوان ضامن دوم ثبت شود.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ضامن خارج از صندوق
            |--------------------------------------------------------------------------
            */

            if (
                $this->guarantor_type ===
                GuarantorType::EXTERNAL->value
            ) {
                if (empty($this->first_name)) {
                    $validator->errors()->add(
                        'first_name',
                        'نام ضامن الزامی است.'
                    );
                }

                if (empty($this->last_name)) {
                    $validator->errors()->add(
                        'last_name',
                        'نام خانوادگی ضامن الزامی است.'
                    );
                }

                if (empty($this->national_code)) {
                    $validator->errors()->add(
                        'national_code',
                        'کد ملی ضامن الزامی است.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [

            'loan_id.required' =>
                'انتخاب وام الزامی است.',

            'loan_id.exists' =>
                'وام انتخاب‌شده معتبر نیست.',

            'guarantor_order.required' =>
                'ترتیب ضامن الزامی است.',

            'guarantor_order.integer' =>
                'ترتیب ضامن باید عدد صحیح باشد.',

            'guarantor_order.in' =>
                'ترتیب ضامن باید ۱ یا ۲ باشد.',

            'guarantor_type.required' =>
                'نوع ضامن الزامی است.',

            'guarantor_type.enum' =>
                'نوع ضامن انتخاب‌شده معتبر نیست.',

            'customer_id.exists' =>
                'عضو صندوق انتخاب‌شده معتبر نیست.',

            'first_name.string' =>
                'نام ضامن باید به صورت متن وارد شود.',

            'first_name.max' =>
                'نام ضامن نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'last_name.string' =>
                'نام خانوادگی ضامن باید به صورت متن وارد شود.',

            'last_name.max' =>
                'نام خانوادگی ضامن نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'national_code.digits' =>
                'کد ملی باید ۱۰ رقم باشد.',

            'mobile.digits' =>
                'شماره موبایل باید ۱۱ رقم باشد.',

            'guarantee_type.required' =>
                'نوع مدرک ضمانت الزامی است.',

            'guarantee_type.enum' =>
                'نوع مدرک ضمانت انتخاب‌شده معتبر نیست.',

            'guarantee_amount.integer' =>
                'مبلغ ضمانت باید عدد صحیح باشد.',

            'guarantee_amount.min' =>
                'مبلغ ضمانت باید بیشتر از صفر باشد.',

            'guarantee_number.string' =>
                'شماره مدرک ضمانت باید به صورت متن وارد شود.',

            'guarantee_number.max' =>
                'شماره مدرک ضمانت نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guarantee_account_number.string' =>
                'شماره حساب ضمانت باید به صورت متن وارد شود.',

            'guarantee_account_number.max' =>
                'شماره حساب ضمانت نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
        ];
    }
}
