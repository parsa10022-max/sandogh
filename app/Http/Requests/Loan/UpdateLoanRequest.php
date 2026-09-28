<?php

namespace App\Http\Requests\Loan;

use App\Enums\GuaranteeType;
use App\Enums\GuarantorType;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateLoanRequest extends FormRequest
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
            | اطلاعات وام
            |--------------------------------------------------------------------------
            */

            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'loan_type_id' => [
                'required',
                'exists:loan_types,id',
            ],

            'loan_number' => [
                'required',
                'integer',
                Rule::unique('loans')
                    ->ignore($this->route('loan')->id)
                    ->where(fn ($query) => $query->where(
                        'loan_type_id',
                        $this->loan_type_id
                    )),
            ],

            'start_date' => [
                'required',
                'string',
            ],

            'loan_amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'installment_count' => [
                'required',
                'integer',
                'min:1',
            ],

            'installment_interval' => [
                'required',
                'integer',
                'in:1,3,6',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /*
            |--------------------------------------------------------------------------
            | ضامن اول
            |--------------------------------------------------------------------------
            */

            'guarantor1_customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'guarantor1_guarantee_type' => [
                'required',
                new Enum(GuaranteeType::class),
            ],

            /*
            |--------------------------------------------------------------------------
            | ضامن دوم
            |--------------------------------------------------------------------------
            */

            'guarantor2_type' => [
                'required',
                new Enum(GuarantorType::class),
            ],

            'guarantor2_customer_id' => [
                'nullable',
                'exists:customers,id',
            ],

            'guarantor2_first_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'guarantor2_last_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'guarantor2_national_code' => [
                'nullable',
                'digits:10',
            ],

            'guarantor2_mobile' => [
                'nullable',
                'digits:11',
            ],

            'guarantor2_guarantee_type' => [
                'required',
                new Enum(GuaranteeType::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'انتخاب وام‌گیرنده الزامی است.',
            'customer_id.exists' => 'وام‌گیرنده انتخاب‌شده معتبر نیست.',

            'loan_type_id.required' => 'نوع وام الزامی است.',
            'loan_type_id.exists' => 'نوع وام انتخاب‌شده معتبر نیست.',

            'loan_number.required' => 'شماره وام الزامی است.',
            'loan_number.integer' => 'شماره وام باید عدد صحیح باشد.',
            'loan_number.unique' => 'این شماره وام قبلاً برای این نوع وام ثبت شده است.',

            'start_date.required' => 'تاریخ شروع وام الزامی است.',
            'start_date.string' => 'تاریخ شروع وام معتبر نیست.',

            'loan_amount.required' => 'مبلغ وام الزامی است.',
            'loan_amount.numeric' => 'مبلغ وام باید عددی باشد.',
            'loan_amount.min' => 'مبلغ وام باید بیشتر از صفر باشد.',

            'installment_count.required' => 'تعداد اقساط الزامی است.',
            'installment_count.integer' => 'تعداد اقساط باید عدد صحیح باشد.',
            'installment_count.min' => 'تعداد اقساط باید حداقل ۱ باشد.',

            'installment_interval.required' => 'فاصله اقساط الزامی است.',
            'installment_interval.integer' => 'فاصله اقساط باید عدد صحیح باشد.',
            'installment_interval.in' => 'فاصله اقساط انتخاب‌شده معتبر نیست.',

            'description.string' => 'توضیحات باید به صورت متن وارد شود.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',

            'guarantor1_customer_id.required' => 'انتخاب ضامن اول الزامی است.',
            'guarantor1_customer_id.exists' => 'ضامن اول انتخاب‌شده معتبر نیست.',

            'guarantor1_guarantee_type.required' => 'نوع ضمانت ضامن اول الزامی است.',
            'guarantor1_guarantee_type.enum' => 'نوع ضمانت ضامن اول معتبر نیست.',

            'guarantor2_type.required' => 'نوع ضامن دوم الزامی است.',
            'guarantor2_type.enum' => 'نوع ضامن دوم معتبر نیست.',

            'guarantor2_customer_id.exists' => 'ضامن دوم انتخاب‌شده معتبر نیست.',

            'guarantor2_first_name.string' => 'نام ضامن دوم باید به صورت متن وارد شود.',
            'guarantor2_first_name.max' => 'نام ضامن دوم نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guarantor2_last_name.string' => 'نام خانوادگی ضامن دوم باید به صورت متن وارد شود.',
            'guarantor2_last_name.max' => 'نام خانوادگی ضامن دوم نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guarantor2_national_code.digits' => 'کد ملی ضامن دوم باید ۱۰ رقم باشد.',

            'guarantor2_mobile.digits' => 'شماره موبایل ضامن دوم باید ۱۱ رقم باشد.',

            'guarantor2_guarantee_type.required' => 'نوع ضمانت ضامن دوم الزامی است.',
            'guarantor2_guarantee_type.enum' => 'نوع ضمانت ضامن دوم معتبر نیست.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
            |--------------------------------------------------------------------------
            | ضامن دوم عضو صندوق
            |--------------------------------------------------------------------------
            */

            if (
                $this->guarantor2_type === GuarantorType::CUSTOMER->value &&
                empty($this->guarantor2_customer_id)
            ) {
                $validator->errors()->add(
                    'guarantor2_customer_id',
                    'کد مشتری ضامن دوم الزامی است.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ضامن دوم غیرعضو
            |--------------------------------------------------------------------------
            */

            if ($this->guarantor2_type === GuarantorType::EXTERNAL->value) {

                if (empty($this->guarantor2_first_name)) {
                    $validator->errors()->add(
                        'guarantor2_first_name',
                        'نام ضامن الزامی است.'
                    );
                }

                if (empty($this->guarantor2_last_name)) {
                    $validator->errors()->add(
                        'guarantor2_last_name',
                        'نام خانوادگی ضامن الزامی است.'
                    );
                }

                if (empty($this->guarantor2_national_code)) {
                    $validator->errors()->add(
                        'guarantor2_national_code',
                        'کد ملی ضامن الزامی است.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | وام‌گیرنده نباید ضامن خودش باشد
            |--------------------------------------------------------------------------
            */

            if (
                $this->customer_id &&
                $this->guarantor1_customer_id &&
                $this->customer_id == $this->guarantor1_customer_id
            ) {
                $validator->errors()->add(
                    'guarantor1_customer_id',
                    'وام‌گیرنده نمی‌تواند ضامن اول خودش باشد.'
                );
            }

            if (
                $this->guarantor2_type === GuarantorType::CUSTOMER->value &&
                $this->customer_id &&
                $this->guarantor2_customer_id &&
                $this->customer_id == $this->guarantor2_customer_id
            ) {
                $validator->errors()->add(
                    'guarantor2_customer_id',
                    'وام‌گیرنده نمی‌تواند ضامن دوم خودش باشد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | یک نفر نمی‌تواند هر دو ضامن باشد
            |--------------------------------------------------------------------------
            */

            if (
                $this->guarantor2_type === GuarantorType::CUSTOMER->value &&
                $this->guarantor1_customer_id &&
                $this->guarantor2_customer_id &&
                $this->guarantor1_customer_id == $this->guarantor2_customer_id
            ) {
                $validator->errors()->add(
                    'guarantor2_customer_id',
                    'یک عضو نمی‌تواند همزمان ضامن اول و ضامن دوم باشد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | هر مشتری فقط یک وام فعال می‌تواند داشته باشد
            |--------------------------------------------------------------------------
            */

            $loan = $this->route('loan');

            if ($loan) {
                $hasActiveLoan = Loan::query()
                    ->where('customer_id', $this->customer_id)
                    ->where('status', LoanStatus::ACTIVE)
                    ->where('id', '!=', $loan->id)
                    ->exists();

                if ($hasActiveLoan) {
                    $validator->errors()->add(
                        'customer_id',
                        'این عضو در حال حاضر یک وام فعال دارد.'
                    );
                }
            }
        });
    }
}
