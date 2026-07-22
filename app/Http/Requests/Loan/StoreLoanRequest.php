<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [

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

        ];
    }
}
