<?php

namespace App\Http\Requests\Customer\SavingsTransfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'membership_number' => [
                'required',
                'integer',
                'exists:customers,membership_number',
            ],

            'amount' => [
                'required',
                'integer',
                'min:1000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'membership_number' => 'شماره عضویت',
            'amount' => 'مبلغ',
        ];
    }
}
