<?php

namespace App\Http\Requests\Customer;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
        return [
            'customer_code' => ['required', 'integer', 'unique:customers,customer_code'],

            'first_name' => ['required', 'string', 'max:50'],

            'last_name' => ['required', 'string', 'max:50'],

            'father_name' => ['nullable', 'string', 'max:50'],

            'national_code' => [
                'required',
                'digits:10',
                'unique:customers,national_code',
            ],

            'mobile' => [
                'required',
                'digits:11',
                'unique:customers,mobile',
            ],

            'mobile_second' => [
                'nullable',
                'digits:11',
            ],

            'status' => [
                'required',
                new Enum(CustomerStatus::class),
            ],
        ];
    }
}
