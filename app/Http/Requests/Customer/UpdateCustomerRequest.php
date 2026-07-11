<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customer = $this->route('customer');

        return [

            'customer_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'customer_code')->ignore($customer),
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'father_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'national_code' => [
                'required',
                'digits:10',
                Rule::unique('customers', 'national_code')->ignore($customer),
            ],

            'mobile' => [
                'required',
                'digits:11',
                Rule::unique('customers', 'mobile')->ignore($customer),
            ],

            'mobile_second' => [
                'nullable',
                'digits:11',
            ],

        ];
    }

}
