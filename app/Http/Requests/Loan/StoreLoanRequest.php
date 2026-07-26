<?php

namespace App\Http\Requests\Loan;

use App\Enums\GuaranteeType;
use App\Enums\GuarantorType;
use App\Enums\LoanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreLoanRequest extends FormRequest
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
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
            |--------------------------------------------------------------------------
            | هر مشتری فقط یک وام فعال می‌تواند داشته باشد
            |--------------------------------------------------------------------------
            */

            $hasActiveLoan = \App\Models\Loan::query()
                ->where('customer_id', $this->customer_id)
                ->where('status', LoanStatus::ACTIVE->value)
                ->exists();

            if ($hasActiveLoan) {
                $validator->errors()->add(
                    'customer_id',
                    'این عضو در حال حاضر یک وام فعال دارد.'
                );
            }

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
            | ضامن دوم خارج از صندوق
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
            | وام‌گیرنده نباید ضامن اول خودش باشد
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

            /*
            |--------------------------------------------------------------------------
            | وام‌گیرنده نباید ضامن دوم خودش باشد
            |--------------------------------------------------------------------------
            */

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

        });
    }
}
