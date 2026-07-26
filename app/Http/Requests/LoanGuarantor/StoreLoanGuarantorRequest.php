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

            /*
            |--------------------------------------------------------------------------
            | وام
            |--------------------------------------------------------------------------
            */

            'loan_id' => [
                'required',
                'exists:loans,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | اطلاعات ضامن
            |--------------------------------------------------------------------------
            */

            'guarantor_order' => [
                'required',
                'integer',
                'in:1,2',
            ],

            'guarantor_type' => [
                'required',
                new Enum(GuarantorType::class),
            ],

            /*
            |--------------------------------------------------------------------------
            | عضو صندوق
            |--------------------------------------------------------------------------
            */

            'customer_id' => [
                'nullable',
                'exists:customers,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | ضامن غیر عضو
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | مدرک ضمانت
            |--------------------------------------------------------------------------
            */

            'guarantee_type' => [
                'required',
                new Enum(GuaranteeType::class),
            ],

        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
            |--------------------------------------------------------------------------
            | ضامن اول باید عضو صندوق باشد
            |--------------------------------------------------------------------------
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
            | غیر عضو صندوق
            |--------------------------------------------------------------------------
            */

            if ($this->guarantor_type === GuarantorType::OTHER->value) {

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

            'loan_id.required' => 'انتخاب وام الزامی است.',
            'loan_id.exists' => 'وام انتخاب شده معتبر نیست.',

            'guarantor_order.required' => 'ترتیب ضامن الزامی است.',
            'guarantor_order.in' => 'ترتیب ضامن باید ۱ یا ۲ باشد.',

            'guarantor_type.required' => 'نوع ضامن الزامی است.',

            'customer_id.exists' => 'عضو صندوق انتخاب شده معتبر نیست.',

            'first_name.max' => 'نام حداکثر ۱۰۰ کاراکتر است.',
            'last_name.max' => 'نام خانوادگی حداکثر ۱۰۰ کاراکتر است.',

            'national_code.digits' => 'کد ملی باید ۱۰ رقم باشد.',

            'mobile.digits' => 'شماره موبایل باید ۱۱ رقم باشد.',

            'guarantee_type.required' => 'نوع مدرک ضمانت الزامی است.',
        ];
    }
}
