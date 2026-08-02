<?php

namespace App\Http\Requests\LoanRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],

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
            'customer_id' => 'عضو',
            'requested_amount' => 'مبلغ درخواستی',
            'description' => 'توضیحات',
        ];
    }
}
