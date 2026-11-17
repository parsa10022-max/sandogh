<?php

namespace App\Http\Requests\Account;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'amount' => clean_money($this->input('amount')),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => [
                'required',
                'integer',
                'in:' . PaymentMethod::CASH->value . ',' . PaymentMethod::BANK_TRANSFER->value,
            ],

            'amount' => [
                'required',
                'integer',
                'min:1',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'iban' => [
                'nullable',
                'required_if:payment_method,' . PaymentMethod::BANK_TRANSFER->value,
                function ($attribute, $value, $fail) {
                    if (
                        (int) $this->input('payment_method') === PaymentMethod::BANK_TRANSFER->value
                        && filled($value)
                        && !\App\Support\Iban::isValid($value)
                    ) {
                        $fail('شماره شبا معتبر نیست.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'انتخاب روش برداشت الزامی است.',
            'payment_method.integer' => 'روش برداشت معتبر نیست.',
            'payment_method.in' => 'روش برداشت انتخاب‌شده معتبر نیست.',

            'amount.required' => 'مبلغ برداشت الزامی است.',
            'amount.integer' => 'مبلغ برداشت معتبر نیست.',
            'amount.min' => 'مبلغ برداشت باید بیشتر از صفر باشد.',

            'description.string' => 'توضیحات باید به صورت متن وارد شود.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'iban.required_if' => 'برای انتقال بانکی وارد کردن شماره شبا الزامی است.',
        ];
    }
}
