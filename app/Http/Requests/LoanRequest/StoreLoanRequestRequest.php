<?php

namespace App\Http\Requests\LoanRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requested_amount' => clean_money(
                $this->input('requested_amount')
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                'integer',
                'exists:customers,id',
            ],

            'requested_amount' => [
                'required',
                'integer',
                'min:10000000',
                'max:200000000',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'عضو صندوق',
            'requested_amount' => 'مبلغ درخواستی',
            'description' => 'توضیحات',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'عضو صندوق را انتخاب کنید.',
            'customer_id.integer' => 'عضو انتخاب‌شده معتبر نیست.',
            'customer_id.exists' => 'عضو انتخاب‌شده در صندوق وجود ندارد.',

            'requested_amount.required' => 'مبلغ وام را وارد کنید.',
            'requested_amount.integer' => 'مبلغ وام باید عددی باشد.',
            'requested_amount.min' => 'حداقل مبلغ درخواست وام ۱۰ میلیون ریال است.',
            'requested_amount.max' => 'حداکثر مبلغ درخواست وام ۲۰۰ میلیون ریال است.',

            'description.string' => 'توضیحات باید به صورت متن وارد شود.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ];
    }
}
