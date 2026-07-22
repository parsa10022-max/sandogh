<?php

namespace App\Http\Requests\Loan;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLoanRequest extends FormRequest
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
        $loan = $this->route('loan');

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
                    ))
                    ->ignore($loan),
            ],

            'loan_amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'start_date' => [
                'required',
                'string',
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
