<?php
namespace App\Http\Requests\LoanRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerLoanRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
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
            'requested_amount' => 'مبلغ درخواستی',
            'description' => 'توضیحات',
        ];
    }

    public function messages(): array
    {
        return [
            'requested_amount.required' =>
                'مبلغ وام را وارد کنید.',

            'requested_amount.integer' =>
                'مبلغ وام باید عددی باشد.',

            'requested_amount.min' =>
                'حداقل مبلغ درخواست وام ۱۰,۰۰۰,۰۰۰ ریال است.',

            'requested_amount.max' =>
                'حداکثر مبلغ درخواست وام ۲۰۰,۰۰۰,۰۰۰ ریال است.',

            'description.max' =>
                'توضیحات نمی‌تواند بیشتر از ۱۰۰۰ کاراکتر باشد.',
        ];
    }
}

