<?php

namespace App\Http\Requests\Customer\SavingsTransfer;

use Illuminate\Foundation\Http\FormRequest;

class SearchSavingsTransferRequest extends FormRequest
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
        ];
    }

    public function attributes(): array
    {
        return [
            'membership_number' => 'شماره عضویت',
        ];
    }
}
