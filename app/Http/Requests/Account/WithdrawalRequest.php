<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\PaymentMethod;


class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'amount' => [
                'required',
                'integer',
                'min:500000',
            ],



            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'iban' => [
                'required',
                function ($attribute, $value, $fail) {

                    if (!\App\Support\Iban::isValid($value)) {
                        $fail('شماره شبا معتبر نیست.');
                    }

                },
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'amount.required' => 'مبلغ برداشت الزامی است.',

            'amount.integer' => 'مبلغ برداشت معتبر نیست.',

            'amount.min' => 'حداقل مبلغ برداشت ۵۰۰,۰۰۰ ریال است.',

            'payment_method.required' => 'روش برداشت را انتخاب کنید.',

            'payment_method.in' => 'روش برداشت معتبر نیست.',

        ];
    }
}
