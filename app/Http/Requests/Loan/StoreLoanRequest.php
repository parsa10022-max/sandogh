<?php

namespace App\Http\Requests\Loan;

use App\Enums\GuaranteeType;
use App\Enums\GuarantorType;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Morilog\Jalali\Jalalian;
use Throwable;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $data = [];

        $numericFields = [
            'loan_amount',
            'guarantor1_guarantee_amount',
            'guarantor2_guarantee_amount',
            'loan_number',
            'installment_count',
            'installment_interval',
            'customer_id',
            'loan_type_id',
            'loan_request_id',
            'guarantor1_customer_id',
            'guarantor2_customer_id',
            'guarantor2_national_code',
            'guarantor2_mobile',
        ];

        foreach ($numericFields as $field) {
            $value = $this->input($field);

            if (! is_string($value) && ! is_int($value)) {
                continue;
            }

            $data[$field] = $this->normalizeDigits((string) $value);

            if (in_array($field, [
                'loan_amount',
                'guarantor1_guarantee_amount',
                'guarantor2_guarantee_amount',
            ], true)) {
                $data[$field] = str_replace(
                    [',', '٬', '،', ' ', ' '],
                    '',
                    $data[$field]
                );
            }
        }

        $startDate = $this->input('start_date');

        if (is_string($startDate)) {
            $data['start_date'] = $this->normalizeDigits($startDate);
        }

        $this->merge($data);
    }

    private function normalizeDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2',
            '۳' => '3', '۴' => '4', '۵' => '5',
            '۶' => '6', '۷' => '7', '۸' => '8',
            '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2',
            '٣' => '3', '٤' => '4', '٥' => '5',
            '٦' => '6', '٧' => '7', '٨' => '8',
            '٩' => '9',
        ]);
    }

    private function validJalaliDate(string $date): bool
    {
        if (! preg_match('/\A\d{4}\/\d{2}\/\d{2}\z/', $date)) {
            return false;
        }

        try {
            $parsedDate = Jalalian::fromFormat('Y/m/d', $date);

            return $parsedDate->format('Y/m/d') === $date;
        } catch (Throwable) {
            return false;
        }
    }

    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | اطلاعات وام
            |--------------------------------------------------------------------------
            */

            'loan_request_id' => [
                'nullable',
                'integer',
                'exists:loan_requests,id',
            ],

            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'loan_type_id' => [
                'required',
                'integer',
                'exists:loan_types,id',
            ],

            'loan_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('loans')->where(
                    fn ($query) => $query->where(
                        'loan_type_id',
                        $this->input('loan_type_id')
                    )
                ),
            ],

            'start_date' => [
                'bail',
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (! is_string($value) || ! $this->validJalaliDate($value)) {
                        $fail('تاریخ شروع وام معتبر نیست.');
                    }
                },
            ],

            'loan_amount' => [
                'bail',
                'required',
                'integer',
                'regex:/\A\d+\z/',
                'min:10000000',
                'max:200000000',
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
                'integer',
                'exists:customers,id',
            ],

            'guarantor1_guarantee_type' => [
                'required',
                new Enum(GuaranteeType::class),
            ],

            'guarantor1_guarantee_amount' => [
                'nullable',
                'integer',
                'regex:/\A\d+\z/',
                'min:0',
            ],

            'guarantor1_guarantee_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'guarantor1_guarantee_account_number' => [
                'nullable',
                'string',
                'max:100',
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
                'integer',
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

            'guarantor2_guarantee_amount' => [
                'nullable',
                'integer',
                'regex:/\A\d+\z/',
                'min:0',
            ],

            'guarantor2_guarantee_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'guarantor2_guarantee_account_number' => [
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'loan_request_id.integer' => 'شناسه درخواست وام معتبر نیست.',
            'loan_request_id.exists' => 'درخواست وام انتخاب‌شده وجود ندارد.',

            'customer_id.required' => 'انتخاب وام‌گیرنده الزامی است.',
            'customer_id.integer' => 'شناسه وام‌گیرنده معتبر نیست.',
            'customer_id.exists' => 'وام‌گیرنده انتخاب‌شده معتبر نیست.',

            'loan_type_id.required' => 'انتخاب نوع وام الزامی است.',
            'loan_type_id.integer' => 'نوع وام معتبر نیست.',
            'loan_type_id.exists' => 'نوع وام انتخاب‌شده معتبر نیست.',

            'loan_number.required' => 'شماره وام الزامی است.',
            'loan_number.integer' => 'شماره وام باید عدد صحیح باشد.',
            'loan_number.min' => 'شماره وام معتبر نیست.',
            'loan_number.unique' => 'این شماره وام قبلاً برای این نوع وام ثبت شده است.',

            'start_date.required' => 'تاریخ شروع وام الزامی است.',

            'loan_amount.required' => 'مبلغ وام الزامی است.',
            'loan_amount.integer' => 'مبلغ وام باید عدد صحیح و بدون اعشار باشد.',
            'loan_amount.regex' => 'مبلغ وام باید فقط شامل رقم باشد.',
            'loan_amount.min' => 'مبلغ وام نباید کمتر از ۱۰ میلیون ریال باشد.',
            'loan_amount.max' => 'مبلغ وام نباید بیشتر از ۲۰۰ میلیون ریال باشد.',

            'installment_count.required' => 'تعداد اقساط الزامی است.',
            'installment_count.integer' => 'تعداد اقساط باید عدد صحیح باشد.',
            'installment_count.min' => 'تعداد اقساط باید حداقل ۱ باشد.',

            'installment_interval.required' => 'فاصله اقساط الزامی است.',
            'installment_interval.integer' => 'فاصله اقساط باید عدد صحیح باشد.',
            'installment_interval.in' => 'فاصله اقساط انتخاب‌شده معتبر نیست.',

            'description.string' => 'توضیحات باید به صورت متن وارد شود.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',

            'guarantor1_customer_id.required' => 'انتخاب ضامن اول الزامی است.',
            'guarantor1_customer_id.integer' => 'شناسه ضامن اول معتبر نیست.',
            'guarantor1_customer_id.exists' => 'ضامن اول انتخاب‌شده معتبر نیست.',

            'guarantor1_guarantee_type.required' => 'نوع ضمانت ضامن اول الزامی است.',
            'guarantor1_guarantee_type.enum' => 'نوع ضمانت ضامن اول معتبر نیست.',

            'guarantor1_guarantee_amount.integer' => 'مبلغ ضمانت ضامن اول باید عدد صحیح باشد.',
            'guarantor1_guarantee_amount.regex' => 'مبلغ ضمانت ضامن اول باید فقط شامل رقم باشد.',
            'guarantor1_guarantee_amount.min' => 'مبلغ ضمانت ضامن اول نمی‌تواند منفی باشد.',

            'guarantor1_guarantee_number.string' => 'شماره ضمانت ضامن اول باید به صورت متن وارد شود.',
            'guarantor1_guarantee_number.max' => 'شماره ضمانت ضامن اول نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guarantor1_guarantee_account_number.string' => 'شماره حساب ضمانت ضامن اول باید به صورت متن وارد شود.',
            'guarantor1_guarantee_account_number.max' => 'شماره حساب ضمانت ضامن اول نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guarantor2_type.required' => 'نوع ضامن دوم الزامی است.',
            'guarantor2_type.enum' => 'نوع ضامن دوم معتبر نیست.',

            'guarantor2_customer_id.integer' => 'شناسه ضامن دوم معتبر نیست.',
            'guarantor2_customer_id.exists' => 'ضامن دوم انتخاب‌شده معتبر نیست.',

            'guarantor2_first_name.string' => 'نام ضامن دوم باید به صورت متن وارد شود.',
            'guarantor2_first_name.max' => 'نام ضامن دوم نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guarantor2_last_name.string' => 'نام خانوادگی ضامن دوم باید به صورت متن وارد شود.',
            'guarantor2_last_name.max' => 'نام خانوادگی ضامن دوم نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guarantor2_national_code.digits' => 'کد ملی ضامن دوم باید ۱۰ رقم باشد.',
            'guarantor2_mobile.digits' => 'شماره موبایل ضامن دوم باید ۱۱ رقم باشد.',

            'guarantor2_guarantee_type.required' => 'نوع ضمانت ضامن دوم الزامی است.',
            'guarantor2_guarantee_type.enum' => 'نوع ضمانت ضامن دوم معتبر نیست.',

            'guarantor2_guarantee_amount.integer' => 'مبلغ ضمانت ضامن دوم باید عدد صحیح باشد.',
            'guarantor2_guarantee_amount.regex' => 'مبلغ ضمانت ضامن دوم باید فقط شامل رقم باشد.',
            'guarantor2_guarantee_amount.min' => 'مبلغ ضمانت ضامن دوم نمی‌تواند منفی باشد.',

            'guarantor2_guarantee_number.string' => 'شماره ضمانت ضامن دوم باید به صورت متن وارد شود.',
            'guarantor2_guarantee_number.max' => 'شماره ضمانت ضامن دوم نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'guarantor2_guarantee_account_number.string' => 'شماره حساب ضمانت ضامن دوم باید به صورت متن وارد شود.',
            'guarantor2_guarantee_account_number.max' => 'شماره حساب ضمانت ضامن دوم نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $customerId = $this->input('customer_id');

            if (
                is_scalar($customerId) &&
                ctype_digit((string) $customerId) &&
                (int) $customerId > 0
            ) {
                $hasActiveLoan = Loan::query()
                    ->where('customer_id', (int) $customerId)
                    ->where('status', LoanStatus::ACTIVE->value)
                    ->exists();

                if ($hasActiveLoan) {
                    $validator->errors()->add(
                        'customer_id',
                        'این عضو در حال حاضر یک وام فعال دارد.'
                    );
                }
            }

            if (
                $this->input('guarantor2_type') === GuarantorType::CUSTOMER->value &&
                empty($this->input('guarantor2_customer_id'))
            ) {
                $validator->errors()->add(
                    'guarantor2_customer_id',
                    'کد مشتری ضامن دوم الزامی است.'
                );
            }

            if (
                $this->input('guarantor2_type') === GuarantorType::CUSTOMER->value &&
                $this->input('guarantor1_customer_id') &&
                $this->input('guarantor2_customer_id') &&
                (string) $this->input('guarantor1_customer_id') ===
                (string) $this->input('guarantor2_customer_id')
            ) {
                $validator->errors()->add(
                    'guarantor2_customer_id',
                    'یک عضو نمی‌تواند همزمان ضامن اول و ضامن دوم باشد.'
                );
            }

            if ($this->input('guarantor2_type') === GuarantorType::EXTERNAL->value) {
                if (empty($this->input('guarantor2_first_name'))) {
                    $validator->errors()->add(
                        'guarantor2_first_name',
                        'نام ضامن الزامی است.'
                    );
                }

                if (empty($this->input('guarantor2_last_name'))) {
                    $validator->errors()->add(
                        'guarantor2_last_name',
                        'نام خانوادگی ضامن الزامی است.'
                    );
                }

                if (empty($this->input('guarantor2_national_code'))) {
                    $validator->errors()->add(
                        'guarantor2_national_code',
                        'کد ملی ضامن الزامی است.'
                    );
                }
            }

            if (
                $this->input('customer_id') &&
                $this->input('guarantor1_customer_id') &&
                (string) $this->input('customer_id') ===
                (string) $this->input('guarantor1_customer_id')
            ) {
                $validator->errors()->add(
                    'guarantor1_customer_id',
                    'وام‌گیرنده نمی‌تواند ضامن اول خودش باشد.'
                );
            }

            if (
                $this->input('guarantor2_type') === GuarantorType::CUSTOMER->value &&
                $this->input('customer_id') &&
                $this->input('guarantor2_customer_id') &&
                (string) $this->input('customer_id') ===
                (string) $this->input('guarantor2_customer_id')
            ) {
                $validator->errors()->add(
                    'guarantor2_customer_id',
                    'وام‌گیرنده نمی‌تواند ضامن دوم خودش باشد.'
                );
            }
        });
    }
}
